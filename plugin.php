<?php
/*
Plugin Name: Custom Title Editor
Plugin URI: https://yourls.org
Description: 設定画面からブラウザのページタイトルを自由に変更できるプラグインです。
Version: 1.1
Author: Oimo
Author URI: 
*/

// 各種フックの登録
yourls_add_filter( 'html_title', 'cte_change_html_title' );
yourls_add_action( 'plugins_loaded', 'cte_init' );

// 1. タイトルを書き換える関数
function cte_change_html_title( $title ) {
    $custom_title = yourls_get_option( 'cte_custom_title' );
    if ( $custom_title ) {
        return $custom_title;
    }
    return $title;
}

// 2. プラグインの初期化（設定画面の登録と保存処理）
function cte_init() {
    // ページの出力が始まる前に保存処理を実行する
    if ( isset( $_POST['cte_submit'] ) && isset( $_GET['page'] ) && $_GET['page'] == 'custom_title_editor' ) {
        yourls_verify_nonce( 'cte_settings' );

        $new_title = trim( $_POST['cte_title'] );
        yourls_update_option( 'cte_custom_title', $new_title );
        
        // 保存後、設定画面へリダイレクトしてタイトルを即時更新させる
        yourls_redirect( yourls_admin_url( 'plugins.php?page=custom_title_editor&success=1' ) );
        exit;
    }

    yourls_register_plugin_page( 'custom_title_editor', 'Custom Title 設定', 'cte_display_page' );
}

// 3. 設定画面の描画
function cte_display_page() {
    // リダイレクト後に成功メッセージを表示
    if ( isset( $_GET['success'] ) ) {
        echo '<div class="success"><p>タイトルを更新しました！</p></div>';
    }

    // 現在のタイトルを取得（未設定なら空）
    $current_title = yourls_get_option( 'cte_custom_title', '' );
    
    // HTMLの属性として安全に出力するためにエスケープ
    $safe_title = yourls_esc_attr( $current_title );
    
    // Nonceの生成
    $nonce = yourls_create_nonce( 'cte_settings' );

    // 設定画面のHTML出力
    echo <<<HTML
    <div id="cte-setting-page" style="margin: 20px 0; max-width: 600px;">
        <h2>Custom Title Editor 設定</h2>
        <p>ブラウザのタブや検索エンジンに表示される、YOURLSのページタイトルを設定します。</p>
        
        <form method="post">
            <input type="hidden" name="nonce" value="$nonce" />
            <p>
                <label for="cte_title"><strong>新しいページタイトル:</strong></label><br />
                <input type="text" id="cte_title" name="cte_title" value="$safe_title" style="width:100%; padding:8px; margin-top:5px;" placeholder="例: マイ短縮URLサービス" />
            </p>
            <p>
                <input type="submit" name="cte_submit" value="設定を保存" class="button primary" />
            </p>
        </form>
    </div>
HTML;
}
