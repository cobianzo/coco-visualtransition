// the default npx wp-scripts plugin-zip had some small errors:
// it doesnt include src and doesnt use my readme-plugin.txt renamed into readme.txt
// so I create my own
// Depends on package 'archiver' and helper ./version-helpers.js

// Usage:
// node ./bin/build-plugin.js [--skip-compression*]
// eg: node ./bin/build-plugin.js
// eg: node ./bin/build-plugin.js --skip-compression

const fs = require('fs').promises;
const fsSync = require('fs');
const archiver = require('archiver');
const path = require('path');

class PluginBuilder {
	/**
	 * @param {string} [version] - The version of the plugin to be built. If not provided,
	 * the version will be determined by reading the package.json file.
	 *
	 * @description
	 * Constructor for the PluginBuilder class.
	 * This class is responsible for building the plugin distribution.
	 * It will create a zip archive of the plugin with the given version.
	 * The zip archive will contain the following files and directories:
	 * - src: The source code of the plugin.
	 * - assets: The plugin's assets (CSS, images, etc).
	 * - inc: The plugin's PHP files.
	 * - screenshots: The plugin's screenshots.
	 * - build: The plugin's compiled files (JS, CSS, etc).
	 * - {pluginSlug}.php: The plugin's PHP file.
	 * - package.json: The plugin's package.json file.
	 * - readme.txt: The plugin's readme file (renamed from README-plugin.txt).
	 */
	constructor(version = null) {
		// Configuración básica del plugin
		this.pluginSlug = 'coco-visualtransition';
		this.version = version;
		this.distDir = 'dist';

		// Lista de archivos y directorios a incluir
		this.directories = ['inc'];

    // In this case we also have to include the phpstan - bootstrap .php
		this.files = [`${this.pluginSlug}.php`, `phpstan-bootstrap.php`, `package.json`];
		this.files = this.files.map((file) => ({
			source: file,
			target: file,
		}));

		// files that change name from dev > dist: renaming the readme for the plugin.
		this.files.push({
			source: 'README-plugin.txt',
			target: 'readme.txt',
		});
	}

	// Limpia archivos .DS_Store que pueden causar problemas
	async cleanDsStoreFiles() {
		const dsStoreFiles = ['build/.DS_Store', '.DS_Store'];

		for (const file of dsStoreFiles) {
			try {
				await fs.access(file);
				await fs.unlink(file);
				console.log(`Removed ${file}`);
			} catch (error) {
				// Ignoramos errores de archivos que no existen
				if (error.code !== 'ENOENT') {
					throw error;
				}
			}
		}
	}

	// Verifica que todos los archivos y directorios necesarios existan
	async validateFiles() {
		// Verificar directorios
		for (const dir of this.directories) {
			try {
				await fs.access(dir);
			} catch (error) {
				console.warn(`Warning: Directory '${dir}' not found, skipping...`);
				// Removemos el directorio de la lista
				this.directories = this.directories.filter((d) => d !== dir);
			}
		}

		// Verificar archivos individuales
		for (const file of this.files) {
			try {
				await fs.access(file.source);
			} catch (error) {
				throw new Error(`Required file '${file.source}' not found!`);
			}
		}
	}

	// Crea el directorio dist si no existe
	async createDistDirectory() {
		try {
			await fs.access(this.distDir);
		} catch {
			await fs.mkdir(this.distDir);
			console.log(`Created ${this.distDir} directory`);
		}
	}

	// Crea el archivo zip
	async createZip() {
		const zipFileName = `${this.distDir}/${this.pluginSlug}.zip`;
		const output = fsSync.createWriteStream(zipFileName);
		const archive = archiver('zip', { zlib: { level: 9 } });

		// Manejamos eventos del archiver para mejor feedback
		archive.on('warning', function (err) {
			if (err.code === 'ENOENT') {
				console.warn('Warning:', err);
			} else {
				throw err;
			}
		});

		archive.on('error', function (err) {
			throw err;
		});

		return new Promise((resolve, reject) => {
			output.on('close', () => {
				console.log(`Created ${zipFileName} (${archive.pointer()} bytes)`);
				resolve(zipFileName);
			});

			archive.pipe(output);

			// Añadir directorios
			for (const dir of this.directories) {
				archive.directory(dir, dir);
			}

			// Añadir archivos individuales
			for (const file of this.files) {
				archive.file(file.source, { name: file.target });
			}

			archive.finalize();
		});
	}

	async copyToDirectory() {
		const targetDirectory = `${this.distDir}`;

		try {
			// Crear el directorio destino si no existe
			if (!fsSync.existsSync(targetDirectory)) {
				await fs.mkdir(targetDirectory, { recursive: true });
			}

			// Copiar directorios
			for (const dir of this.directories) {
				const targetDirPath = path.join(targetDirectory, path.basename(dir));
				console.log(`Copiando directorio: ${dir} -> ${targetDirPath}`);
				await fs.cp(dir, targetDirPath, { recursive: true });
			}

			// Copiar archivos individuales
			for (const file of this.files) {
				const targetFilePath = path.join(targetDirectory, file.target);
				console.log(`Copiando archivo: ${file.source} -> ${targetFilePath}`);
				// Crear el directorio del archivo si no existe
				await fs.mkdir(path.dirname(targetFilePath), { recursive: true });
				await fs.copyFile(file.source, targetFilePath);
			}

			console.log(`Archivos copiados exitosamente a: ${targetDirectory}`);
		} catch (err) {
			console.error('Ocurrió un error al copiar los archivos:', err);
			throw err;
		}
	}

	// Método principal que ejecuta todo el proceso
	async build(compress = true) {
		try {
			console.log(`Building plugin version ${this.version}...`);

			await this.cleanDsStoreFiles();
			await this.validateFiles();
			await this.createDistDirectory();
			console.log('Build completed successfully!');

			if (!compress) {
				console.log('skipping compression. Creating in dir: ' + this.distDir);
				this.copyToDirectory();
				return this.distDir;
			} else {
				console.log('Build compressed!');
				const zipFile = await this.createZip();
				return zipFile;
			}
		} catch (error) {
			console.error('Build failed:', error.message);
			throw error;
		}
	}
}

// Función principal para ejecutar el script
async function main() {
	// Obtener la versión del argumento de línea de comandos (not used anymore, I prefer to extract it.)
	//  const version = process.argv[2] && !process.argv[2].startsWith('--') ? process.argv[2] : null;

	const { extractVersion } = require('./version-helpers');
	const version = extractVersion(path.join(__dirname, '../', 'coco-visualtransition.php'));

	const skipCompression = [process.argv[2], process.argv[3]].includes('--skip-compression');

	if (!version) {
		console.error('Warning: Version parameter has not been specified');
		// process.exit(1);
	} else {
		// Validar formato de versión
		if (!/^\d+\.\d+\.\d+$/.test(version)) {
			console.error('Error: Version must be in format X.Y.Z (e.g., 1.0.0)');
			process.exit(1);
		}
	}

	try {
		const builder = new PluginBuilder(version ?? null);
		await builder.build(!skipCompression);
	} catch (error) {
		console.error('Build process failed:', error);
		process.exit(1);
	}

  console.log('Build process completed successfully! Opening folder in 5s');
  setTimeout( () => {
    const { exec } = require('child_process');
    // Open the /dist folder in Finder (macOS specific)
    exec('open ./dist', (error, stdout, stderr) => { /** I culd handle any error here  */});
  }, 5000);

}

// Ejecutar solo si es llamado directamente
if (require.main === module) {
	main();
}

module.exports = PluginBuilder;
