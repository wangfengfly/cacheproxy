CacheProxy is used for redis cache, example:
$proxy = new CacheProxy(new User_Model(), CacheProxy::TYPE_W);
$proxy->UserByIdS(array(1,2,3));

then proxy will get/set redis value for you.

