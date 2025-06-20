// used in version-increment.js
const fs = require('fs');

/**
 * Extract the current version from a file.
 * @param {string} filePath - Path to the file.
 * @return {string} - The current version.
 */
function extractVersion(filePath) {
	const content = fs.readFileSync(filePath, 'utf-8');
	const match = content.match(/\b\d+\.\d+\.\d+\b/); // Matches versions like 1.0.3
	if (!match) {
		throw new Error(`No version found in ${filePath}`);
	}
	return match[0];
}

module.exports = { extractVersion };
