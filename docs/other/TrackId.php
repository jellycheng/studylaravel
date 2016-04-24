<?php

/**
* 追踪ID
* url中参数： _trackid
*/
class TrackId
{
    /**
     * 追踪ID
     * @var string
     */
    protected $sTrackId;

    /**
     * 创建追踪ID
     * 28968669-16DB-0CA3-F617-06F797822EF0
     * @author Sinute
     * @date   2015-04-19
     */
    public function generate()
    {
        return strtoupper(preg_replace(
            '~^(.{8})(.{4})(.{4})(.{4})(.{12})$~',
            '\1-\2-\3-\4-\5',
            md5(uniqid('', true))
        ));
    }

    /**
     * 获取追踪ID
     *
     * @author Sinute
     * @date   2015-04-19
     * @return string
     */
    public function get()
    {
        if (!$this->sTrackId) {
            $this->sTrackId = $this->generate();
        }
        return $this->sTrackId;
    }

    /**
     * 设置追踪ID
     *
     * @author Sinute
     * @date   2015-04-22
     * @param  string     $sTrackId 追踪ID
     */
    public function set($sTrackId)
    {
        $this->sTrackId = $sTrackId;
    }
}


$obj = new TrackId();
echo $obj->get();

