// rollup.config.js
import fs from 'fs';
import path from 'path';
import alias from '@rollup/plugin-alias';
import commonjs from '@rollup/plugin-commonjs';
import replace from '@rollup/plugin-replace';
import typescript from 'rollup-plugin-typescript2';
import json from '@rollup/plugin-json';
import babel from 'rollup-plugin-babel';
import { terser } from 'rollup-plugin-terser';
import minimist from 'minimist';
import dts from 'rollup-plugin-dts';

// Get browserslist config and remove ie from es build targets
const esbrowserslist = fs.readFileSync('./.browserslistrc')
    .toString()
    .split('\n')
    .filter((entry) => entry && entry.substring(0, 2) !== 'ie');

const argv = minimist(process.argv.slice(2));

const projectRoot = path.resolve(__dirname, '..');

const baseConfig = {
    input: 'public/entry.ts',
    plugins: {
        preVue: [
            alias({
                resolve: ['.js', '.jsx', '.ts', '.tsx'],
                entries: {
                    '@': path.resolve(projectRoot, 'public'),
                },
            }),
        ],
        replace: {
            'process.env.NODE_ENV': JSON.stringify('production'),
            'process.env.ES_BUILD': JSON.stringify('false'),
        },
        babel: {
            exclude: 'node_modules/**',
            extensions: ['.js', '.jsx', '.ts', '.tsx'],
        },
    },
};

// ESM/UMD/IIFE shared settings: externals
// Refer to https://rollupjs.org/guide/en/#warning-treating-module-as-external-dependency
const external = [
    // list external dependencies, exactly the way it is written in the import statement.
    // eg. 'jquery'
    'vue',
    'vuex',
    'modulesMetadata',
    'core',
    'get',
    'uniq',
    'capitalize',
    'clone',
    'Jsona',
    'simplePropertyMappers',
    'Ajv',
    'uuid',
];

// UMD/IIFE shared settings: output.globals
// Refer to https://rollupjs.org/guide/en#output-globals for details
const globals = {
    // Provide global variable names to replace your external imports
    // eg. jquery: '$'
    vue: 'Vue',
    vuex: 'Vuex',
};

// Customize configs for individual targets
const buildFormats = [];
if (!argv.format || argv.format === 'es') {
    const esConfig = {
        ...baseConfig,
        external,
        output: {
            file: 'dist/devices-module.esm.js',
            format: 'esm',
            exports: 'named',
        },
        plugins: [
            replace({
                ...baseConfig.plugins.replace,
                'process.env.ES_BUILD': JSON.stringify('true'),
            }),
            ...baseConfig.plugins.preVue,
            typescript(),
            babel({
                ...baseConfig.plugins.babel,
                presets: [
                    [
                        '@babel/preset-env',
                        {
                            targets: esbrowserslist,
                        },
                    ],
                ],
            }),
            commonjs(),
            json(),
        ],
    };
    buildFormats.push(esConfig);
}

if (!argv.format || argv.format === 'cjs') {
    const umdConfig = {
        ...baseConfig,
        external,
        output: {
            compact: true,
            file: 'dist/devices-module.ssr.js',
            format: 'cjs',
            name: 'DevicesModule',
            exports: 'named',
            globals,
        },
        plugins: [
            replace(baseConfig.plugins.replace),
            ...baseConfig.plugins.preVue,
            babel(baseConfig.plugins.babel),
            commonjs(),
        ],
    };
    buildFormats.push(umdConfig);
}

if (!argv.format || argv.format === 'iife') {
    const unpkgConfig = {
        ...baseConfig,
        external,
        output: {
            compact: true,
            file: 'dist/devices-module.min.js',
            format: 'iife',
            name: 'DevicesModule',
            exports: 'named',
            globals,
        },
        plugins: [
            replace(baseConfig.plugins.replace),
            ...baseConfig.plugins.preVue,
            babel(baseConfig.plugins.babel),
            commonjs(),
            terser({
                output: {
                    ecma: 5,
                },
            }),
        ],
    };
    buildFormats.push(unpkgConfig);
}

buildFormats.push({
    // path to your declaration files root
    input: './dist/lib/types.d.ts',
    output: {
        file: 'dist/devices-module.d.ts',
        format: 'es',
    },
    plugins: [
        dts(),
    ],
});


// Export config
export default buildFormats;
