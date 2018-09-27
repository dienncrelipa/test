<ul class="nav nav-tabs" id="adminTabChecker" role="tablist">
  <li class="{{ Request::is('webapp/admin') ? 'active' : '' }}" role="presentation"><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::SITE_ADMIN) }}">記事のスタータス</a></li>
  <li class="{{ Request::is('webapp/admin/checktitle*') ? 'active' : '' }}" role="presentation"><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::ADMIN_TITLE) }}" >CMSで記事重複</a></li>
  <li class="{{ Request::is('webapp/admin/checklink*') ? 'active' : '' }}" role="presentation"><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::ADMIN_TARGET) }}" >リンクがない公開記事</a></li>
  <li class="{{ Request::is('webapp/admin/statusPosts*') ? 'active' : '' }}" role="presentation"><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::ADMIN_POSTS) }}" >1CMS複数ターゲットサイト</a></li>
</ul>