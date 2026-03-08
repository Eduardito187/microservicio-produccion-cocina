import js from '@eslint/js';

export default [
	js.configs.recommended,
	{
		files: ['**/*.js'],
		ignores: [
			'vendor/**',
			'node_modules/**',
			'public/**',
			'storage/**',
		],
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
