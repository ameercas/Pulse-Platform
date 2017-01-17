var paths = {
  root_dir: '../',
  js_dest: '../assets/js/',
  css_dest: '../assets/css/'
}

module.exports = function (grunt)
{

  /*  Load tasks  */

  require('load-grunt-tasks')(grunt);

  /*  Configure project  */

  grunt.initConfig(
  {
    pkg: grunt.file.readJSON('package.json'),

    // Setup tasks
    image_resize:      require('../build/tasks/image_resize')(),
    sprite:        require('../build/tasks/sprite')(),
    copy:          require('../build/tasks/copy')(),
    cssUrlRewrite:     require('../build/tasks/cssUrlRewrite')(),
    less:          require('../build/tasks/less')(),
    concat:        require('../build/tasks/concat')(paths.js_dest),
    concat_css:      require('../build/tasks/concat_css')(paths.css_dest),
    uglify:        require('../build/tasks/uglify')(),
    cssmin:        require('../build/tasks/cssmin')(),
    imagemin:        require('../build/tasks/imagemin')(),
    clean:         require('../build/tasks/clean')(),
    watch:         require('../build/tasks/watch')()
  });

  /*  Register tasks  */

  /*
   * --------------------------------
   * Default for app and login
   * --------------------------------
   */
  grunt.registerTask('default_styles', [
    'image_resize:widgets_xs',/* // Only necessary when changing sprite */
    'copy:app_styles',
    'sprite:widgets_xs',
    'cssUrlRewrite:app1',
    'cssUrlRewrite:app2',
    'cssUrlRewrite:app3',
    'cssUrlRewrite:app4',
    'cssUrlRewrite:app5',
    'less:app',
    'concat_css:app_css',
/*    'concat_css:app_css_production',*/
    'concat_css:frontend_css', 
    'cssmin:app_css', 
    'cssmin:frontend_css', 
    /*'imagemin', */
    'clean:app',
    'copy:app_styles_after',
  ]);

  grunt.registerTask('default_scripts', [
    'concat:app_js', 
/*    'concat:app_js_production', */
    'concat:frontend_js',
    'uglify:app_js',
/*    'uglify:app_js_production',*/
    'uglify:frontend_js',
    'clean:app'
  ]);

  /*
   * --------------------------------
   * Frontend (login, registration)
   * --------------------------------
   */
  grunt.registerTask('frontend', [
    'concat:frontend_js',
    'uglify:frontend_js',
    'concat_css:frontend_css',
    'cssmin:frontend_css'
  ]);

  /*
   * --------------------------------
   * elfinder (popup)
   * --------------------------------
   */
  grunt.registerTask('elfinder', [
    'concat:elfinder_js',
    'concat_css:elfinder_css',
    'uglify:elfinder_js',
    'cssmin:elfinder_css'
  ]);

  /*
   * --------------------------------
   * Public site
   * --------------------------------
   */
   /*
  grunt.registerTask('site', [
    'concat:site_js',
    'uglify:site_js',
    'concat_css:site_css',
    'cssmin:site_css'
  ]);
*/
  /*
   * --------------------------------
   * Mobile site assets
   * --------------------------------
   */
  grunt.registerTask('mobile_site', [
    'copy:mobile_site', 
    'concat:mobile_site_js',
    'concat_css:mobile_site_css',
    'uglify:mobile_site_js',
    'cssmin:mobile_site_css'
  ]);

  /*
   * --------------------------------
   * One Page Builder editor + user site general
   * --------------------------------
   */
  grunt.registerTask('one_page_editor', [
    'copy:one_page_editor',
    'cssUrlRewrite:app3',  
    'cssUrlRewrite:one_page_editor',  
    'less:one_page_editor',
    'concat:one_page_editor_jquery',  
    'concat:one_page_editor_plugins', 
    'concat:one_page_editor_site_jquery', 
    'concat:one_page_editor_site_plugins', 
    'concat_css:one_page_editor_css',
    'concat_css:one_page_editor_global_css',
    'uglify:one_page_editor_jquery',
    'uglify:one_page_editor_plugins',
    'uglify:one_page_editor_site_jquery', 
    'uglify:one_page_editor_site_plugins',
    'cssmin:one_page_editor_css',
    'cssmin:one_page_editor_site_css',
    'concat:one_page_editor_jquery_plugins_js',
    'concat:one_page_editor_site_jquery_plugins_global_js',
    'clean:one_page_editor',
    'copy:app_styles_after'
  ]);

  /*
   * --------------------------------
   * Photo stock optimization
   * --------------------------------
   */
  grunt.registerTask('stock', [
  ]);

};
