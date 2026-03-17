const js = require('@eslint/js');

module.exports = [
	{
		ignores: [
			'vendor/**',
			'node_modules/**',
			'public/**',
			'storage/**',
		],
	},
	{
		files: ['eslint.config.js'],
		languageOptions: {
			globals: {
				require: 'readonly',
				module: 'readonly',
			},
		},
	},
	js.configs.recommended,
	{
		files: ['resources/js/**/*.js'],
		languageOptions: {
			globals: {
				window: 'readonly',
				document: 'readonly',
			},
		},
		rules: {
			'indent': ['error', 'tab'],
			'no-tabs': 'off',
			'no-unused-vars': 'warn',
			'no-console': 'warn',
			'semi': ['error', 'always'],
			'quotes': ['error', 'single'],
			'eqeqeq': ['error', 'always'],
			'no-var': 'error',
			'prefer-const': 'error',
		},
	},
];
