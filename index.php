<?php
// 日本語の文字化け対策
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// --- 安全対策：POSTメソッドでない場合は処理を中断 ---
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "不正なアクセスです。";
    exit;
}

// --- フォームから送られてきたデータを変数に格納 ---
// htmlspecialchars() を使ってXSS(クロスサイトスクリプティング)対策
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
$date = htmlspecialchars($_POST['date'], ENT_QUOTES, 'UTF-8');
$time = htmlspecialchars($_POST['time'], ENT_QUOTES, 'UTF-8');

// --- サーバーサイドでの入力チェック（空欄でないか、メール形式が正しいか） ---
if (empty($name) || empty($email) || empty($date) || empty($time) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "入力内容に誤りがあります。フォームに戻って正しく入力してください。";
    exit;
}

// --- PayPal決済リンクの作成 ---
$paypal_email = 'your_paypal_account@example.com'; // ★★★ あなたのPayPalアカウントのメールアドレスに変更 ★★★
$price = 2000; // ★★★ 料金を設定 ★★★
$item_name = 'ビワもぎ体験予約（' . $date . ' ' . $time . '）'; // 商品名
$currency_code = 'JPY'; // 通貨コード

// URLエンコードして安全なURLパラメータを作成
$paypal_link = sprintf(
    "https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=%s&item_name=%s&amount=%d¤cy_code=%s&charset=utf-8",
    urlencode($paypal_email),
    urlencode($item_name),
    $price,
    urlencode($currency_code)
);

// --- ユーザーへの自動返信メールを作成 ---
$to_customer = $email;
$subject_customer = "【ビワもぎ予約】お申し込みありがとうございます";
$body_customer = <<<EOT
{$name}様

この度はビワもぎ体験にお申し込みいただき、誠にありがとうございます。
以下の内容でご予約を承りました。

お名前: {$name}
ご希望日時: {$date} {$time}

料金のお支払いをお願いいたします。
以下のリンクからPayPalにて決済を完了してください。

決済リンク: {$paypal_link}

ご来園を心よりお待ちしております。

---
ビワもぎ園（あなたの農園名など）
---
EOT;
$headers = "From: info@your-domain.com"; // ★★★ あなたのサイトのメールアドレスに変更 ★★★

// --- 管理者への通知メールを作成 ---
$to_admin = 'your_admin_email@example.com'; // ★★★ あなたの管理用メールアドレスに変更 ★★★
$subject_admin = "【管理者通知】ビワもぎ予約が入りました";
$body_admin = <<<EOT
新しい予約申し込みがありました。

お名前: {$name}
メールアドレス: {$email}
希望日時: {$date} {$time}
EOT;

// --- メール送信実行 ---
mb_send_mail($to_customer, $subject_customer, $body_customer, $headers);
mb_send_mail($to_admin, $subject_admin, $body_admin, $headers);

// --- ユーザーに完了画面を表示 ---
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お申し込み完了</title>
</head>
<body>
    <h1>お申し込みありがとうございます</h1>
    <p>ご入力いただいたメールアドレス宛に、確認メールを送信いたしました。</p>
    <p>メールに記載された決済リンクより、お手続きを完了してください。</p>
    <p><a href="index.html">トップページに戻る</a></p>
</body>
</html>
