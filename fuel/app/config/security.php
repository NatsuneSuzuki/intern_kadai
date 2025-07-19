<?php
return array(

    // 出力フィルタ（XSS 対策）
    'output_filter' => array('Security::htmlentities'),

    // URI フィルタ（XSS 対策）
    'uri_filter' => array('htmlentities'),

    // 入力フィルタ（必要に応じて指定）
    'input_filter' => array(),

    // 出力自動フィルタリング
    'auto_filter_output' => true,

    // htmlentities のフラグ（省略可：ENT_QUOTES がデフォルト）
    'htmlentities_flags' => ENT_QUOTES,

    // 二重エンコードの防止（必要に応じて）
    'htmlentities_double_encode' => true,

    // セキュリティトークンのソルト（CSRF用）
    'token_salt' => 'your-unique-salt-here',

    // CSRF 設定（必要に応じて）
    'csrf_autoload' => false,
    'csrf_autoload_methods' => array('post', 'put', 'delete'),
    'csrf_bad_request_on_fail' => false,
    'csrf_auto_token' => false,
    'csrf_token_key' => 'fuel_csrf_token',
    'csrf_expiration' => 0,

    // X-Header の許可（必要に応じて）
    'allow_x_headers' => false,

    // 自動フィルタリングを除外するクラス（View や Presenter など）
    'whitelisted_classes' => array(
        'Fuel\\Core\\Presenter',
        'Fuel\\Core\\Response',
        'Fuel\\Core\\View',
        'Fuel\\Core\\ViewModel',
        'Closure',
    ),
);
