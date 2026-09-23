const fs = require('fs');
const vm = require('vm');

const source = fs.readFileSync('upload/admin/view/javascript/common.js', 'utf8');
const start = source.indexOf('window.translit = function');
const end = source.indexOf('const flashSeoField');

if (start === -1 || end === -1 || end <= start) {
	throw new Error('Unable to isolate SEO generator helpers from common.js');
}

let helpers = source.slice(start, end);
helpers = helpers.replace('const buildSeoValue =', 'window.buildSeoValue =');

const context = {
	window: {
		defaultLanguageId: 1,
		languages: {
			1: 'ru-ru',
			2: 'uk-ua'
		},
		seoStoreConfig: {
			0: {
				defaultLanguageId: 1,
				languageScoped: true
			},
			1: {
				defaultLanguageId: 2,
				languageScoped: false
			}
		}
	}
};

vm.createContext(context);
vm.runInContext(helpers, context);

function assertSame(expected, actual, message) {
	if (expected !== actual) {
		throw new Error(message + ': expected ' + JSON.stringify(expected) + ', got ' + JSON.stringify(actual));
	}
}

assertSame(
	'test-tovar',
	context.window.buildSeoValue('Тест товар', 2, 0),
	'SEO Language store must generate a clean non-default-language slug'
);

assertSame(
	'test-tovar',
	context.window.buildSeoValue('Тест товар', 2, 1),
	'Legacy store must not prefix its own catalog default language'
);

assertSame(
	'ru_test-tovar',
	context.window.buildSeoValue('Тест товар', 1, 1),
	'Legacy store must prefix a non-default language using that language code'
);

assertSame(
	'test-tovar',
	context.window.buildSeoValue('Тест товар', 1, 0),
	'Default language slug changed in SEO Language mode'
);

console.log('SEO generator regression checks passed');
