<?php
namespace App\Models;

use Estate\Database\Eloquent\Model;
use ServiceException;

/**
 * Housing
 *
 * @author ResourceMapper
 */
class Housing extends Model
{

    protected $connection = 'aaz_db';
    protected $fillable   = ['iUserID', 'iLoupanID', 'sLoupanName', 'sBaiduLongitude', 'sBaiduLatitude', 'iCityID', 'iAreaID', 'iBlockID', 'sLoupanAddress', 'sLouhao', 'sDanyuan', 'sRoomNo', 'iToward', 'iDecoration', 'iPrice', 'iPayType', 'iRzTime', 'iRoomNum', 'iHallNum', 'iToiletNum', 'iSpace', 'iHousingFloor', 'iTotalFloor', 'sTitle', 'sEquipment', 'iType'];
    protected $orderable  = ['*'];
    protected $rangeable  = ['*'];
    protected $columnable = ['iAutoID', 'iUserID', 'iLoupanID', 'sLoupanName', 'sBaiduLongitude', 'sBaiduLatitude', 'iCityID', 'iAreaID', 'iBlockID', 'sLoupanAddress', 'sLouhao', 'sDanyuan', 'sRoomNo', 'iToward', 'iDecoration', 'iPrice', 'iPayType', 'iRzTime', 'iRoomNum', 'iHallNum', 'iToiletNum', 'iSpace', 'iHousingFloor', 'iTotalFloor', 'sTitle', 'sEquipment', 'iType', 'iStatus', 'iCreateTime', 'iUpdateTime', 'iDeleteTime'];

    protected $table = 'housing';

    /**
     * 查询
     *
     * @param  array      $aWhere       option    字段值
     * @param  integer    $iPerPage     option    分页大小
     * @param  array      $aColumns     option    字段选择
     * @param  array      $aOrders      option    字段排序
     * @param  array      $aRanges      option    字段范围查询
     * @param  integer    $iPerPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function findAll(array $aWhere = [], $iPerPage = 10, array $aColumns = ['*'], array $aOrders = [], array $aRanges = [])
    {
        $oHousing = new static;
        foreach ($aWhere as $sKey => $mValue) {
            $oHousing = $oHousing->where($sKey, $mValue);
        }
        return $oHousing->withOrder($aOrders)->withRange($aRanges)->paginate($iPerPage, $aColumns);
    }

    /**
     * 更新
     *
     * @param  integer    $iAutoID    required    id
     * @param  array      $aData      required    字段值
     * @return integer
     */
    public static function updateByID($iAutoID, array $aData)
    {
        if (!$oHousing = self::find($iAutoID)) {
            throw new ServiceException("ROW_NOT_FOUND");
        }
        return $oHousing->update($aData);
    }

}
