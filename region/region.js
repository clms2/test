// 省市区联动
$(function(){
    /* regions = array(
        region_id => array(
            region_name => '北京',
            child => array(
                region_id => array(region_name => ''),
                region_id => array(region_name => '', child=>array(..))
            )
        )
     )*/
    if(typeof regions == 'undefined'){
        console.log('未定义regions变量');
        return ;
    }
    /**
     * 获取用于填充下拉列表的options
     * @param  {string} province_id 省份id 不传则获取所有省
     * @param  {string} city_id     城市id 不传则获取该省下的所有区域
     * @return {string}             拼接后的html
     */
    window.get_region_option = function(province_id, city_id){
        var temp = '',
            data = regions;
        if(typeof province_id != 'undefined'){
            data = regions[province_id]['child'] || '';
            if(!data) return '';
        }
        if(typeof city_id != 'undefined'){
            data = regions[province_id]['child'][city_id]['child'] || '';
            if(!data) return '';
        }

        for(var region_id in data){
            temp += '<option value="'+region_id+'">'+data[region_id]['region_name']+'</option>';
        }

        return temp;
    }

    var tpl = {
        'opt_city' : '<option value="0">请选择城市</option>',
        'opt_area' : '<option value="0">请选择区域</option>',
        'sel_area' : '<div class="ui-select">\
                        <select id="area" name="area">\
                            <option value="0" selected>请选择区域</option>\
                        </select>\
                    </div>'
    }
    // 省份联动
    $("body").delegate('#province', 'change', function() {
        var province_id = $(this).val();
        if(province_id == 0){
            $("#city,#area").attr('disabled', 'disabled');
            return;
        }
        $("#city").removeAttr('disabled').html(tpl.opt_city+get_region_option(province_id));

        // 改变省份 重置区域 并禁止选择
        $("#area").html(tpl.sel_area).attr('disabled', 'disabled');
    });
    // 城市联动
    $("body").delegate('#city', 'change', function() {
        var province_id = $("#province").val(),
            city_id = $(this).val();
        if(city_id == 0){
            $("#area").attr('disabled', 'disabled');
            return;
        }
        var area_data = get_region_option(province_id, city_id);

        // 有下级区域可选
        if(area_data != ''){
            $("#area").parent().css('visibility', 'visible').end().removeAttr('disabled').html(tpl.opt_area+area_data);
        }else{
            // 没下级就隐藏
            $("#area").parent().css('visibility', 'hidden');
        }
    });

    // 省份默认值
    var default_province = $("#province").data('default-code'),
        default_city = $("#city").data('default-code'),
        default_area = $("#area").data('default-code');

    $("#province").append(get_region_option());
    if(default_province){
        $("#province").val(default_province).change();
        if(default_city){
            $("#city").val(default_city).change();
            if(default_area){
                $("#area").val(default_area);
            }
        }
    }
})