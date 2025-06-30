/**
 * Calling this script updates the version of the plugin in
 * every file where it is declared.
 *
 *
 */
// Usage: `node ./bin/version-increment.js` (uses patch by default)
// Usage: `node ./bin/version-increment.js minor`
// Usage: `node ./bin/version-increment.js major`

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import semver from 'semver';
import { extractVersion } from './version-helpers.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Paths to your files
const FILES = {
	pluginPHP: path.join(__dirname, '../', 'coco-visualtransition.php'),
	packageJSON: path.join(__dirname, '../', 'package.json'),
	composerJSON: path.join(__dirname, '../', 'composer.json'),
	readmeTXT: path.join(__dirname, '../', 'README-plugin.txt'),
};

/**
 * Update version in a file based on the type of file.
 * @param {string} filePath       - Path to the file.
 * @param {string} currentVersion - Current version to replace.
 * @param {string} newVersion     - New version to insert.
 * @param {boolean} [silent=false] - If true, the function will not log anything to the console.
 */
function updateFileVersion(filePath, currentVersion, newVersion, silent = false) {
	const content = fs.readFileSync(filePath, 'utf-8');
  let updatedContent = '--error--';
  // in the entry php file there are two occurrences of the version, so we check for all of them.
  if (filePath == FILES.pluginPHP) {
    updatedContent = content.replaceAll(currentVersion, newVersion);
    if (!silent) {
      const matches = content.match(new RegExp(currentVersion, 'gi'));
      const count = matches ? matches.length : 0;
      console.log(`Updated ${count} occurrence${count !== 1 ? 's' : ''} of version in ${filePath}`);
    }
  } else {
    // in the package, plugin.txt and composer, we only replace the first occurrence of the version.
    updatedContent = content.replace(currentVersion, newVersion, { flags: 'i' });
    if (!silent) console.log(`Updated version in ${filePath}`);
  }
	fs.writeFileSync(filePath, updatedContent, 'utf-8');
}

/**
 * Main function to handle the version update.
 * @param {string} incrementType - Type of version increment (patch, minor, major).
 * @param {boolean} [silent=false] - If true, the function will not log anything to the console.
 */
function updateVersion(incrementType = 'patch', silent = false) {
	try {
		if (!silent) {
			console.log(`Reading current version from ${FILES.pluginPHP}`);
		}

		// Extract the current version from plugin.php (source of truth)
		const currentVersion = extractVersion(FILES.pluginPHP);
		if (!silent) {
			console.log(`Current version: ${currentVersion}`);
		}

		// Calculate the new version
		const newVersion = semver.inc(currentVersion, incrementType);
		if (!newVersion) {
			throw new Error(`Failed to calculate new version from ${currentVersion}`);
		}
		if (!silent) {
			console.log(`New version: ${newVersion}`);
		}

		// Update all files
		updateFileVersion(FILES.pluginPHP, currentVersion, newVersion, silent);
		updateFileVersion(FILES.packageJSON, currentVersion, newVersion, silent);
		updateFileVersion(FILES.composerJSON, currentVersion, newVersion, silent);
		updateFileVersion(FILES.readmeTXT, currentVersion, newVersion, silent);

		if (!silent) {
			console.log('Version updated successfully in all files.');
		}

		return newVersion;
	} catch (error) {
		console.error(`Error: ${error.message}`);
		return '';
	}
}

// Command-line arguments for increment type
const args = process.argv.slice(2);
const incrementType = args[0] || 'patch';
if (!['patch', 'minor', 'major'].includes(incrementType)) {
	console.error('Invalid increment type. Use "patch", "minor", or "major".');
	process.exit(1);
}
const silent = process.argv.includes('--silent');

// Execute the updateVersion function
const newVersion = updateVersion(incrementType, silent);

console.log(newVersion);

export default newVersion;
