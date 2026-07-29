/**
 * Upload dist/pack WordPress bundle via FTP (excludes SQL + README).
 * Etapa 2 — optional. See docs/DEPLOY.md and .env.example.
 *
 * Env:
 *   DEPLOY_FTP_HOST
 *   DEPLOY_FTP_USER
 *   DEPLOY_FTP_PASSWORD
 *   DEPLOY_FTP_REMOTE_DIR
 *   DEPLOY_FTP_SECURE      true|false
 *   THEME_SLUG             used to skip {slug}-pack.sql
 */
import { Client } from 'basic-ftp';
import { config as loadEnv } from 'dotenv';
import { existsSync } from 'node:fs';
import { readdir, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

loadEnv({ path: path.join(root, '.env') });

const host = process.env.DEPLOY_FTP_HOST;
const user = process.env.DEPLOY_FTP_USER;
const password = process.env.DEPLOY_FTP_PASSWORD;
const remoteDir = (process.env.DEPLOY_FTP_REMOTE_DIR || '/public_html').replace(
	/\/$/,
	''
);
const secure = String(process.env.DEPLOY_FTP_SECURE || 'false').toLowerCase() === 'true';
const themeSlug = process.env.THEME_SLUG || 'starter';
const localRoot = path.join(root, 'dist/pack');
const sqlName = `${themeSlug}-pack.sql`;

const skipNames = new Set([sqlName, 'README-DEPLOY.txt', '.DS_Store']);

if (!host || !user || !password) {
	console.error(
		'Missing FTP credentials. Copy .env.example → .env and set DEPLOY_FTP_*.'
	);
	process.exit(1);
}

if (!existsSync(path.join(localRoot, 'wp-load.php'))) {
	console.error('Missing WordPress pack in dist/pack. Run: npm run pack:site');
	process.exit(1);
}

/**
 * @param {Client} client
 * @param {string} localDir
 * @param {string} remoteDirPath
 */
async function uploadDir(client, localDir, remoteDirPath) {
	await client.ensureDir(remoteDirPath);
	const entries = await readdir(localDir);

	for (const name of entries) {
		if (skipNames.has(name)) {
			console.log(`    skip ${name}`);
			continue;
		}

		const localPath = path.join(localDir, name);
		const remotePath = `${remoteDirPath}/${name}`;
		const info = await stat(localPath);

		if (info.isDirectory()) {
			await uploadDir(client, localPath, remotePath);
		} else if (info.isFile()) {
			await client.uploadFrom(localPath, remotePath);
		}
	}
}

const client = new Client(120_000);
client.ftp.verbose = process.env.DEPLOY_FTP_VERBOSE === 'true';

try {
	console.log(`==> FTP connect ${host} (secure=${secure})`);
	await client.access({ host, user, password, secure });

	console.log(`==> Upload ${localRoot}`);
	console.log(`    → ${remoteDir}`);
	await uploadDir(client, localRoot, remoteDir);

	console.log('==> FTP upload complete');
	console.log(
		`Import dist/pack/${sqlName} in phpMyAdmin, then create wp-config.php with DB credentials.`
	);
} finally {
	client.close();
}
