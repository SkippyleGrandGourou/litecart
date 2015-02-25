<nav id="site-menu" class="twelve-eighty">
  <?php
    if (!function_exists('custom_draw_site_menu')) {
      function custom_draw_site_menu($items, $indent=0) {
        echo '<ul>' . PHP_EOL;
        foreach ($items as $item) {
          echo '  <li class="'. $item['type'] .'-'. $item['id'] . (!empty($item['active']) ? ' active' : '') .'"><a href="'. htmlspecialchars($item['link']) .'">'. (!empty($item['image']) ? '<img src="'. $item['image'] .'" alt="" style="vertical-align: middle; margin-right: 10px;">' : '') .''. $item['title'] .'</a>';
          if (!empty($item['subitems'])) {
            echo PHP_EOL . custom_draw_site_menu($item['subitems'], $indent+1);
          }
          echo '  </li>' . PHP_EOL;
        }
        echo '</ul>' . PHP_EOL;
      }
    }
    custom_draw_site_menu($items);
  ?>
</nav>