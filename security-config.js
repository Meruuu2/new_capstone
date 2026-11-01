/**
 * Security & Obfuscation Configuration
 * 
 * This file provides utilities for applying encryption, hashing, and obfuscation
 * to the JavaScript build pipeline.
 * 
 * Usage:
 * - Import this config in vite.config.js
 * - Apply obfuscation to production builds
 * - Use crypto-js for runtime encryption needs
 */

// Import security libraries
const CryptoJS = require('crypto-js');
const JavaScriptObfuscator = require('javascript-obfuscator');

/**
 * Obfuscate JavaScript code
 * @param {string} code - JavaScript source code
 * @returns {string} - Obfuscated code
 */
function obfuscateCode(code) {
  return JavaScriptObfuscator.obfuscate(code, {
    compact: true,
    controlFlowFlattening: true,
    controlFlowFlatteningThreshold: 0.75,
    deadCodeInjection: true,
    deadCodeInjectionThreshold: 0.4,
    debugProtection: true,
    debugProtectionInterval: true,
    disableConsoleOutput: true,
    identifierNamesGenerator: 'hexadecimal',
    log: false,
    renameGlobals: false,
    rotateStringArray: true,
    selfDefending: true,
    stringArray: true,
    stringArrayThreshold: 0.75,
    unicodeEscapeSequence: true,
    splitStrings: true,
    splitStringsChunkLength: 10
  }).getObfuscatedCode();
}

/**
 * Hash a string using SHA-256
 * @param {string} data - Data to hash
 * @returns {string} - Hashed value
 */
function hashData(data) {
  return CryptoJS.SHA256(data).toString();
}

/**
 * Generate a random API key/token
 * @param {number} length - Length of the key
 * @returns {string} - Random hex key
 */
function generateSecretKey(length = 32) {
  return CryptoJS.lib.WordArray.random(length / 2).toString();
}

/**
 * Vite plugin for production obfuscation
 * Add to vite.config.js plugins array
 */
const viteObfuscationPlugin = {
  name: 'vite-obfuscation-plugin',
  apply: 'build',
  enforce: 'post',
  async transform(code, id) {
    if (id.endsWith('.js') && !id.includes('node_modules')) {
      try {
        return {
          code: obfuscateCode(code),
          map: null
        };
      } catch (error) {
        console.warn(`Failed to obfuscate ${id}:`, error.message);
        return code; // Fall back to original code if obfuscation fails
      }
    }
  }
};

module.exports = {
  obfuscateCode,
  hashData,
  generateSecretKey,
  viteObfuscationPlugin,
  
  // Export CryptoJS for runtime use
  CryptoJS,
  
  // Export JavaScript Obfuscator for CLI usage
  JavaScriptObfuscator
};
