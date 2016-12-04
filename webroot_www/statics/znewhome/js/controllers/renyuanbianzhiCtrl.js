require(['../statics/znewhome/js/config.js'],function (){
	require(['jquery','utils','modules/common/organization'],function ($,utils,org){
		var funcs={};
		funcs.aaa=function (){
			var data = [{
				id:1,
				name:'郭建伟',
				department:'董事长',
				parendId:''
			},{
				id:12,
				name:'周志轩',
				department:'总经理',
				parendId:'1'

			},{
				id:21,
				name:'佟健',
				department:'人力行政副总经理',
				parendId:'12'

			},{
				id:22,
				name:'张安成',
				department:'财务总监',
				parendId:'12'

			},{
				id:23,
				name:'陈学兵',
				department:'总经理助理',
				parendId:'12'

			},{
				id:31,
				name:'张盛夏',
				department:'人力行政总监',
				parendId:'21'

			},{
				id:32,
				name:'陈学兵',
				department:'人力行政部',
				parendId:'31'

			},{
				id:41,
				name:'陈学兵',
				department:'市场部',
				parendId:'12'

			},{
				id:51,
				name:'陈学兵',
				department:'采购物流部',
				parendId:'23'

			},{
				id:52,
				name:'陈学兵',
				department:'生产运营部',
				parendId:'23'

			},{
				id:53,
				name:'陈学兵',
				department:'安全技术部',
				parendId:'23'

			},{
				id:54,
				name:'陈学兵',
				department:'基建部',
				parendId:'23'

			},{
				id:61,
				name:'陈学兵',
				department:'行政主管',
				parendId:'31'

			},{
				id:62,
				name:'陈学兵',
				department:'人事主管',
				parendId:'31'

			},{
				id:63,
				name:'陈学兵',
				department:'行政合同专员',
				parendId:'31'

			},{
				id:71,
				name:'陈学兵',
				department:'财务副经理',
				parendId:'22'

			},{
				id:72,
				name:'陈学兵',
				department:'出纳',
				parendId:'22'

			}];
			var tmp={};	
			var rootObj = null;
			for(var i=0;i<data.length;i++){
				tmp[data[i].id] = new org.orgNode();
				tmp[data[i].id].customParam.EmpName=data[i].name;
				tmp[data[i].id].customParam.department=data[i].department;
				tmp[data[i].id].customParam.EmpPhoto=data[i].photo;
				if(data[i].parendId===''){
					rootObj = tmp[data[i].id];
				}else{
					tmp[data[i].parendId].Nodes.Add(tmp[data[i].id]);
				}
			}
			var OrgShows=new org.orgShow(rootObj);
			OrgShows.Top=0;
			OrgShows.Left=0;
			OrgShows.IntervalWidth=5;
			OrgShows.IntervalHeight=40;
			//OrgShows.ShowType=2;
			// OrgShows.BoxWidth=100;
			// OrgShows.BoxHeight=100;
			OrgShows.BoxTemplet="<div id=\"{Id}\" class=\"OrgBox\" onclick=\"CookieGroup()\"><h3>{department}</h3><p>{EmpName}</p><span></span></div>"
			OrgShows.Run()
		}
		funcs.init=function (){
			this.aaa();
		}
		funcs.init();
	});
});
