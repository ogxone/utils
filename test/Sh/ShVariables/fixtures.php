<?php
/**
 * Created by PhpStorm.
 * User: valentinyelchenko
 * Date: 10/9/18
 * Time: 11:25 AM
 */
return [
    'simple_variables' => [
        'CACHE_CS_HOST=127.0.0.1',
        'ELASTIC_INDEXER_HOST="http://vpc-motorsport-qa-p7xqhcsykdwl5sm4y7cxwgutpy.us-east-1.es.amazonaws.com:80"',
        'CONTENT_SERVICE_VERSION=134'
    ],
    'list_variables' => [
        'DB_DSN=mysql:dbname=ms_v6_q;host=msv6-aurora.cluster-c85uo2p40u0z.us-east-1.rds.amazonaws.com',
        'DOMAINS_LIST=ru=ru&es=es&ua=ua',
        'COUNTRIES_LIST=?Italy=Italy&US=US'
    ],
    'obj_variables' => [
        'CACHE_RCS_HOST=127.0.0.1',
        'MEMCACHE_HOSTS_OBJ=[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211],["msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]',
        'MEMCACHE_HOSTS=[["msv6-content-v3.4wvmqu.0001.use1.cache.amazonaws.com", 11211],["msv6-content-v3-2.4wvmqu.0001.use1.cache.amazonaws.com", 11211]]',
        'CUSTOM_JSON_OBJ={"opt1": "opt1val", "opt2": 123}'
    ],
    'obj_variables_with_error' => [
        'CUSTOM_JSON_OBJ={"opt", "opt1val", "opt2": 123}'
    ]
];