<?php 
// 数据表字段
// region_code 编号
// parent_region_code 上级编号
public function getRegions(){
    $data = Region::where('region_code', '!=', 1)->select('region_code', 'parent_region_code', 'region_name')->get()->toArray();

    return $this->getMenuTree($data);
}

private function getMenuTree ($arrcat, $parent_region_code = 1 ) {
    $arrtree = array ();
    if (empty ( $arrcat ))
        return false;
    foreach ( $arrcat as $key => $value ) {
        if ($value ['parent_region_code'] == $parent_region_code) {
            unset ( $arrcat [$key] ); // 注销当前节点数据，减少已无用的遍历
            $rid = $value['region_code'];
            $value ['child'] = $this->getMenuTree ($arrcat, $rid);
            if (empty($value ['child'])) { unset($value['child']);}
            // 无需返回的数据
            unset($value['region_code'], $value['parent_region_code']);
            $arrtree [$rid] = $value ;
        }
            
    }
    return $arrtree;
}