/**
 * 身份证工具类
 */
var IdCardUtil = function() {
	this.Wi = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1);// 加权因子 
	this.ValideCode = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
	this.Vcity = {11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",
			23:"黑龙江",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",
			41:"河南",42:"湖北",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",
			52:"贵州",53:"云南",54:"西藏",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",
			71:"台湾",81:"香港",82:"澳门",91:"国外"
 };
};

IdCardUtil.prototype = {
	
	/**
	 * 验证身份证号码
	 * @returns {Boolean}
	 */
	validate : function(card) {
		var reg = /(^\d{15}$)|(^\d{17}(\d|X)$)/;
		if(reg.test(card) === false) {
			return false;
		} else {
			if(this.getProvince(card) == null){
				return false;
			}
			if(this.getBirthday(card) == null){
				return false;
			}
			//15位转18位
			card = this.changeFivteenToEighteen(card);
			var len = card.length;
			if(len == '18') {
				var cardTemp = 0, i, valnum; 
				for(i = 0; i < 17; i ++) { 
					cardTemp += card.substr(i, 1) * this.Wi[i]; 
				} 
				valnum = this.ValideCode[cardTemp % 11]; 
				if (valnum != card.substr(17, 1)) {
					return false;
				}
			}
		}
		return true; 
	},

	/**
	 * 根据号码获得省份
	 * @param card
	 * @returns
	 */
	getProvince : function(card) {
		var province = parseInt(card.substr(0,2));
		if(this.Vcity[province]) {
			return this.Vcity[province];
		}
		return null;
	},
	
	/**
	 * 获取生日
	 * @param card
	 * @returns
	 */
	getBirthday : function(card) {
		var len = card.length;
		if(len == 15) {
			var re_fifteen = /^(\d{6})(\d{2})(\d{2})(\d{2})(\d{3})$/;
			var arr_data = card.match(re_fifteen);
			var year = arr_data[2];
			var month = arr_data[3];
			var day = arr_data[4];
			if(month <= 0 || month > 12){
				return null;
			}
			if(day <= 0 || month > 31){
				return null;
			}
			var birthday = new Date('19'+year+'/'+month+'/'+day);
			return birthday;
		}
		if(len == 18) {
			var re_eighteen = /^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/;
			var arr_data = card.match(re_eighteen);
			var year = arr_data[2];
			var month = arr_data[3];
			var day = arr_data[4];
			if(month <= 0 || month > 12){
				return null;
			}
			if(day <= 0 || month > 31){
				return null;
			}
			var birthday = new Date(year+'/'+month+'/'+day);
			return birthday;
		}
		return null;
	},
	
	/**
	 * 获得性别
	 * @param card
	 * @returns 0:女|1：男
	 */
	getSex : function(card) {
		var len = card.length;
		if(len == 15) {
			var re_fifteen = /^(\d{6})(\d{2})(\d{2})(\d{2})(\d{3})$/;
			var arr_data = card.match(re_fifteen);
			var sex = arr_data[5] % 2;
			return sex;
		}
		if(len == 18) {
			var re_eighteen = /^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/;
			var arr_data = card.match(re_eighteen);
			var sex = arr_data[5] % 2;
			return sex;
		}
		return null;
	},
	
	/**
	 * 15位转18位
	 * @param card
	 * @returns
	 */
	changeFivteenToEighteen : function(card) {
		if(card.length == '15') {
			var cardTemp = 0, i; 
			card = card.substr(0, 6) + '19' + card.substr(6, card.length - 6);
			for(i = 0; i < 17; i ++) { 
				cardTemp += card.substr(i, 1) * this.Wi[i]; 
			} 
			card += this.ValideCode[cardTemp % 11]; 
			return card;
		}
		return card;
	}
};

