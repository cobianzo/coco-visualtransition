import globals from 'globals';

console.log('Checking for AudioWorklet globals:');
const audioWorkletGlobals = Object.keys(globals.browser).filter(key => key.includes('AudioWorklet'));
console.log('Found:', audioWorkletGlobals.map(key => `"${key}" (length: ${key.length})`));

// Check for trailing whitespace
audioWorkletGlobals.forEach(key => {
  if (key !== key.trim()) {
    console.log(`ERROR: Global "${key}" has leading/trailing whitespace!`);
    console.log(`Trimmed version: "${key.trim()}"`);
  }
});

// Check all globals for whitespace issues
const globalsWithWhitespace = Object.keys(globals.browser).filter(key => key !== key.trim());
console.log('\nAll globals with whitespace issues:');
globalsWithWhitespace.forEach(key => {
  console.log(`"${key}" -> "${key.trim()}"`);
});

