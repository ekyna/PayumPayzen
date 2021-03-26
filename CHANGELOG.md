CHANGELOG for 1.5
===

* 1.5.1
    * __Sha256__ is now the <ins>default hash algorithm</ins> used to generate signature. If you still want to use __Sha1__ (previously the default algorithm for SYSTEMPAY endpoint), you must update the API config:
    
        #### Before
      
            $config = [
                'site_id'     => ' ... ',
                'certificate' => ' ... ',
                'ctx_mode'    => Api::MODE_PRODUCTION,
                'directory'   => ' ... ',
            ];
      
        #### After
      
            $config = [
                'site_id'     => ' ... ',
                'certificate' => ' ... ',
                'ctx_mode'    => Api::MODE_PRODUCTION,
                'directory'   => ' ... ',
                'hash_mode'   => Api::HASH_MODE_SHA1, // Add this entry
            ];
