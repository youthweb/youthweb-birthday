module.exports = function(grunt) {
	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		concat: {
			options: {
				separator: ';',
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},
			css: {
				src: [
					'node_modules/font-awesome/css/font-awesome.min.css',
					'node_modules/bootstrap/dist/css/bootstrap.min.css'
				],
				dest: 'public/assets/css/style.css'
			},
			js: {
				src: [
					'node_modules/jquery/dist/jquery.slim.min.js',
					'node_modules/tether/dist/js/tether.min.js',
					'node_modules/bootstrap/dist/js/bootstrap.min.js'
				],
				dest: 'public/assets/js/script.js'
			}
		},
		copy: {
			main: {
				expand: true,
				cwd: 'node_modules/font-awesome/fonts',
				src: '**',
				dest: 'public/assets/fonts/',
			},
		},
	});

	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.registerTask('default', ['concat:css', 'concat:js', 'copy:main']);
};
