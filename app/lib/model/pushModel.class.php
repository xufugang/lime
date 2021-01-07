<?php

if (!defined('IN_XLP')) {
    exit('Access Denied!');
}

/**
 * Description of pushModel
 * 推流相关的模型
 * @author xlp
 */
class pushModel extends model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param bizId 您在腾讯云分配到的bizid
     *        streamId 您用来区别不同推流地址的唯一id
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    function getPushUrl($bizId, $streamId, $key = null, $time = null)
    {

        if ($key && $time) {
            $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
            //txSecret = MD5( KEY + livecode + txTime )
            //livecode = bizid+"_"+stream_id  如 8888_test123456
            $livecode = $bizId . "_" . $streamId; //直播码
            $txSecret = md5($key . $livecode . $txTime);
            $ext_str = "?" . http_build_query(array(
                    "bizid" => $bizId,
                    "txSecret" => $txSecret,
                    "txTime" => $txTime
                ));
        }
        return "rtmp://" . $bizId . ".livepush.myqcloud.com/live/" . $livecode . (isset($ext_str) ? $ext_str : "");
    }

    /**
     * 获取播放地址
     * @param bizId 您在腾讯云分配到的bizid
     *        streamId 您用来区别不同推流地址的唯一id
     * @return array url
     */
    function getPlayUrl($bizId, $streamId)
    {
        $rs = array();
        if ($bizId && $streamId) {
            $livecode = $bizId . "_" . $streamId; //直播码
            $rs = array(
                "rtmp://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode,
                "http://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode . ".flv",
                "http://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode . ".m3u8"
            );
        }
        return $rs;
    }


}
