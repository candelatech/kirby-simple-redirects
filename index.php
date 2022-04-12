<?php
Kirby::plugin('candela/basic-rerouter', [
  'blueprints' => [
      'pages/reroutes' => function () {
          //Here we create our blueprint inside the plugin.
          //This makes the Basic Rerouter super easy to install and use.
          return Data::read(__DIR__ . '/reroutes.yml');
      },
  ],
  'routes' => [
    [
      'pattern' => '(:all)',
      'action'  => function ($uid) {
        $kirby = kirby();
        $site = $kirby->site();

        //Page will be a draft
        $rerouterData = $site->findPageOrDraft('candela-rerouter-data');

        //check if current page URL exists and grab it's url.
        //If page doesn't exist, generate a url with a prepended slash for comparison
        if (page($uid)){
          $page_url = page($uid)->url();
        } else {
          $page_url = '/' . $uid;
        }

        //Loop through redirects on the redirect data page
        foreach($rerouterData->reds()->toStructure() as $red){
          $src_url = $red->source_url()->toString();
          $red_url = $red->dest_url()->toString();

          //Check if src and dest URLs contain a prepended slash. Add one if they don't.
          if ($src_url[0] != '/'){
            $src_url = '/' . $src_url;
          }
          if ($red_url[0] != '/'){
            $red_url = '/' . $red_url;
          }

          //If we find a match, redirect and die.
          if($page_url == $src_url) {
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . $red_url);
            die();
          }
        }

        //If we didn't find a match, move on to the next route.
        $this->next();
      }
    ]
  ],
  'areas' => [
  'candela-rerouter-data' => function ($kirby) {
    //Here we attach the rerouter to the main panel hamburger menu.
    //Page will be a draft
    //It is necessary to create our data page if it does not already exist.
    $page = $kirby->site()->findPageOrDraft('candela-rerouter-data');
    if($page == null){
      Page::create([
        'slug' => 'candela-rerouter-data',
        'template' => 'reroutes',
        'content' => [
          'title' => 'Rerouter'
        ]
      ]);
    }
    return [
      // label for the menu and the breadcrumb
      'label' => 'Reroutes',

      // icon for the menu and breadcrumb
      'icon' => 'road-sign',

      // show / hide from the menu
      'menu' => true,

      // link to the main area view
      'link' => '/pages/candela-rerouter-data',

    ];
  }
]
]);
