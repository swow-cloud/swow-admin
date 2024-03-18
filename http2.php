<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
$ch = \curl_init();

// 设置 URL 及其他适当的选项
\curl_setopt($ch, CURLOPT_URL, 'https://localhost:9501');
\curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
\curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: keep-alive']);

// 获取响应内容
$response = \curl_exec($ch);

// 检查是否有错误发生
if (\curl_errno($ch)) {
    // 如果 curl_exec() 返回 false，就表示发生了错误
    echo 'cURL 错误: ' . \curl_error($ch);
} else {
    // 正常处理响应内容
    echo '响应内容: ' . $response;
}

// 检查是否发送了 HTTP/2 请求
if (\curl_getinfo($ch, CURLINFO_HTTP_VERSION) == CURL_HTTP_VERSION_2_0) {
    echo "HTTP/2 request sent\n";
} else {
    echo "HTTP/2 request not sent\n";
}

// 关闭 cURL 资源，并释放系统资源
\curl_close($ch);

// 输出响应结果
echo $response;
