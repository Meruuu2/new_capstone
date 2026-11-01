/**
 * Writes variant.json so packaged builds know admin | college_staff | org_staff.
 * Usage: node write-variant.js admin|college|org
 */
const fs = require('fs');
const path = require('path');

const short = process.argv[2];
const map = {
  admin: 'admin',
  college: 'college_staff',
  org: 'org_staff',
};
const role = map[short] || short;
const allowed = ['admin', 'college_staff', 'org_staff'];
if (!allowed.includes(role)) {
  console.error('Usage: node write-variant.js <admin|college|org>');
  process.exit(1);
}
const out = path.join(__dirname, 'variant.json');
fs.writeFileSync(out, JSON.stringify({ role }, null, 2), 'utf8');
console.log('Wrote', out, 'role=', role);
