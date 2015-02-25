<?php
  
  class route {
    private static $_classes = array();
    private static $_links_cache = array();
    private static $_links_cache_id = '';
    private static $_routes = array();
    public static $route = array();
    public static $request = '';
    
    //public static function construct() {}
    
    //public static function load_dependencies() {}
    
    public static function initiate() {
      
    // Load cached links (url rewrites)
      self::$_links_cache_id = cache::cache_id('links', array('language'));
      self::$_links_cache = cache::get(self::$_links_cache_id, 'file');
      
    // Add default routes
      $routes = array(
        '#^(?:index\.php)?$#'                  => array('page' => 'index',          'params' => '',  'redirect' => true),
        '#^ajax/(.*)(?:\.php)?$#'              => array('page' => 'ajax/$1',        'params' => '',  'redirect' => true),
        '#^feeds/(.*)(?:\.php)?$#'             => array('page' => 'feeds/$1',       'params' => '',  'redirect' => true),
        '#^order_process$#'                    => array('page' => 'order_process',  'params' => '',  'redirect' => false, 'post_security' => false),
        '#^([0-9|a-z|_]+)(?:\.php)?$#'         => array('page' => '$1',             'params' => '',  'redirect' => true),
        // See ~/includes/routes/ folder for more advanced routes
      );
      
      foreach ($routes as $pattern => $route) {
        self::$_routes[$pattern] = $route;
      }
      
    // Load external/dynamic routes
      $files = glob(FS_DIR_HTTP_ROOT . WS_DIR_ROUTES . 'url_*.inc.php');
      
      foreach($files as $file) {
        $route_name = preg_replace('#^.*/url_(.*)\.inc\.php$#', '$1', $file);
        $class_name = preg_replace('#^.*/(url_.*)\.inc\.php$#', '$1', $file);
        
        self::$_classes[$route_name] = new $class_name;
        
        if (method_exists(self::$_classes[$route_name], 'routes')) {
          $routes = self::$_classes[$route_name]->routes();
          
          if (!empty($routes)) {
            foreach ($routes as $route) {
              self::$_routes[$route['pattern']] = array(
                'page' => $route['page'],
                'params' => !empty($route['params']) ? $route['params'] : '',
                'redirect' => !empty($route['redirect']) ? true : false,
              );
            }
          }
        }
      }
    }
    
    public static function startup() {
      
    // Neutralize request path (removes logical prefixes)
      self::$request = self::strip_url_logic($_SERVER['REQUEST_URI']);
      
    // Abort mission if in admin panel
      if (preg_match('#^'. preg_quote(WS_DIR_ADMIN, '#') .'.*#', self::$request)) return;
      
    // Set target route for requested URL
      foreach (self::$_routes as $matched_pattern => $route) {
        
        if (!preg_match($matched_pattern, self::$request)) continue;
          
        $route['page'] = preg_replace($matched_pattern, $route['page'], self::$request);
        
        if (!empty($route['params'])) {
          parse_str(preg_replace($matched_pattern, $route['params'], self::$request), $params);
          $_GET = array_merge($_GET, $params);
        }
        
        self::$route = $route;
        break;
      }
      
    // Forward to rewritten URL (if necessary)
      if (!empty(self::$route['page'])) {
        
        $rewritten_url = self::rewrite(document::ilink(self::$route['page'], $_GET));
        
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != parse_url($rewritten_url, PHP_URL_PATH)) {
          
          $do_redirect = true;
          
        // Don't forward if there is HTTP POST data
          if (!empty($_POST)) $do_redirect = false;
          
        // Don't forward if requested not to
          if (isset(self::$route['redirect']) && self::$route['redirect'] == true) $do_redirect = false;
          
        // Don't forward if there are notices in stack
          if (!empty(notices::$data)) {
            foreach (notices::$data as $notices) {
              if (!empty($notices)) $do_redirect = false;
            }
          }
          
          if ($do_redirect) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '. $rewritten_url);
            exit;
          }
        }
      }
    }
    
    public static function before_capture() {
    }    
    
    public static function after_capture() {
      cache::set(self::$_links_cache_id, 'file', self::$_links_cache);
    }
    
    //public static function prepare_output() {}
    
    //public static function before_output() {}
    
    //public static function after_shutdown() {}
    
    //public static function shutdown() {}
    
    ######################################################################
    
    public static function ilink($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return link::create_link($document, $new_params, $inherit_params, $skip_params, $language_code);
    }
    
    public static function href_ilink($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return htmlspecialchars(self::link($document, $new_params, $inherit_params, $skip_params, $language_code));
    }
    
    public static function strip_url_logic($link) {
      
      if (empty($link)) return;
      
      $link = parse_url($link, PHP_URL_PATH);
      
      $link = preg_replace('#^'. WS_DIR_HTTP_HOME .'(index\.php/)?(('. implode('|', array_keys(language::$languages)) .')/)?(.*)$#', "$4", $link);
      
      return $link;
    }
    
    public static function rewrite($link, $language_code=null) {
      
      if (empty($language_code)) $language_code = language::$selected['code'];
      
      if (!in_array($language_code, array_keys(language::$languages))) {
        trigger_error('Invalid language code ('. $language_code .')', E_USER_WARNING);
        return;
      }
      
      if (isset(self::$_links_cache[$language_code][$link])) return self::$_links_cache[$language_code][$link];
      
      $parsed_link = link::explode_link($link);
      
    // Don't override links for external domains
      if ($parsed_link['host'] != preg_replace('#^([a-z|0-9|\.|-]+)(?:\:[0-9]+)?$#', '$1', $_SERVER['HTTP_HOST'])) return;
      
      ###
      
    // Strip logic from string
      $parsed_link['path'] = self::strip_url_logic($parsed_link['path']);
      
    // Don't rewrite links in the admin folder
      if (preg_match('#^'. preg_quote(basename(WS_DIR_ADMIN), '#') .'.*#', $parsed_link['path'])) return;
      
    // Set route name
      $route_name = preg_replace('#^(.*)$/#', '$1', $parsed_link['path']);
      
      if (!empty(self::$_classes[$route_name])) {
      
      // Rewrite url
        if (method_exists(self::$_classes[$route_name], 'rewrite')) {
        
          $rewritten_parsed_link = self::$_classes[$route_name]->rewrite($parsed_link, $language_code);
          
          if (!empty($rewritten_parsed_link)) {
            $parsed_link = $rewritten_parsed_link;
            $parsed_link['path'] = self::strip_url_logic($parsed_link['path']);
          }
        }
      }
      
      ###
      
    // Set home path (Platform root)
      $http_route_base = WS_DIR_HTTP_HOME;
      
    // Append router base (/index.php or /)
      if (!isset($_SERVER['HTTP_MOD_REWRITE']) || !in_array(strtolower($_SERVER['HTTP_MOD_REWRITE']), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
        $http_route_base .= 'index.php/';
      }
      
    // Append language prefix
      if (settings::get('seo_links_language_prefix')) {
        $http_route_base .= $language_code .'/';
      }
      
      $parsed_link['path'] = $http_route_base . $parsed_link['path'];
      self::$_links_cache[$language_code][$link] = link::implode_link($parsed_link);
      
      return self::$_links_cache[$language_code][$link];
    }
  }
  
?>