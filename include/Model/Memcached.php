<?php
class Memcached{
    private $_memcache = null;
   
    public function __construct($configStr=''){
    	if(empty($configStr))return false;
    	if(RUNONSAE){
    		$this->_memcache=memcache_init();
    	}elseif(RUNONBAE){
    		require_once ('BaeMemcache.class.php');
    		$this->_memcache= new BaeMemcache();
    	}else{
    		$configs=explode('|', $configStr);
    		$this->_memcache = new Memcache;
        	$this->_memcache->connect($configs[0], isset($configs[1])?$configs[1]:11211, isset($configs[2])?$configs[2]:10);
    	}
    }
    public function get($name){
        $value = $this->_memcache->get($name);
        return $value;
    }
    public function set($name, $value, $ext1 = false, $ttl= 0){
          return $this->_memcache->set($name, $value, $ext1, $ttl);
    }
    public function add($name, $value, $ext1 = false, $ttl= 0){
        return $this->_memcache->add($name, $value,$ext1, $ttl);
    }
    public function delete($name){ 
        return $this->_memcache->delete($name);
    }
    public function close(){
        return $this->_memcache->close();
    }
    public function increment($name,$value){
        return $this->_memcache->increment($name, $vlaue);
    }
    public function decrement($name,$value){
        return $this->_memcache->decrement($name, $vlaue);
    }
    public function getExtendedStats(){
        return $this->_memcache->getExtendedStats();
    }
    public function getStats(){
        return $this->_memcache->getStats();
    }
    public function flush(){
        return $this->_memcache->flush();
    }
}