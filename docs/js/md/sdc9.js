// Modify: 8/04/2015
// Js Version: pingan.all.1.08

function WebTrends(){
	// begin: user modifiable
	var a=this;
	a.dcsid="dcs82b9ujitigdu3gaykxw0hn_5p6b";
	a.domain="sdc.pingan.com";
	a.timezone=8;
	a.enabled=true;
	a.i18n=true;
	a.paidsearchparams="gclid";
    a.splitvalue="";
	a.preserve=false;
	a.cookieTypes = "all";
	a.FPCConfig = {
		enabled: (a.cookieTypes === "all" || a.cookieTypes == "firstPartyOnly"),
		name: "WT-FPC",
		domain: location.host.replace(/^[A-z]+\./g,".").replace(/[:0-9]+$/g,""),
		expires: 315360000000
	};
	a.TPCConfig = {
		enabled: (a.cookieTypes === "all"),
		cfgType: (a.cookieTypes === "all") ? "":"1"
	};
	// end: user modifiable
	a.DCS={};
	a.WT={};
	a.DCSext={};
	a.images=[];
	a.index=0;

	a.exre=(function(){return(window.RegExp?new RegExp("dcs(uri)|(ref)|(aut)|(met)|(sta)|(sip)|(pro)|(byt)|(dat)|(p3p)|(cfg)|(redirect)|(cip)","i"):"");})();
	a.re=(function(){return(window.RegExp?(a.i18n?{"%25":/\%/g,"%26":/\&/g}:{"%09":/\t/g,"%20":/ /g,"%23":/\#/g,"%26":/\&/g,"%2B":/\+/g,"%3F":/\?/g,"%5C":/\\/g,"%22":/\"/g,"%7F":/\x7F/g,"%A0":/\xA0/g}):"");})();

    //add by zhengzy
    a.lt = {};
    a.ltv = {};
    a.qry = {};
    a.max=800;
}
WebTrends.prototype.dcsGetId=function(){
	if (this.enabled&&(document.cookie.indexOf(this.FPCConfig.name+"=")==-1)&&(document.cookie.indexOf("WTLOPTOUT=")==-1)&&this.TPCConfig.enabled&&typeof(gWtId)=="undefined"&&typeof(gTempWtId)=="undefined"){
		document.write("<scr"+"ipt type='text/javascript' src='"+"http"+(window.location.protocol.indexOf('https:')==0?'s':'')+"://"+this.domain+"/"+this.dcsid+"/wtid.js"+"'><\/scr"+"ipt>");
	}
};
WebTrends.prototype.dcsGetIdAsync=function(){
	if (this.enabled&&(document.cookie.indexOf(this.FPCConfig.name+"=")==-1)&&(document.cookie.indexOf("WTLOPTOUT=")==-1)&&this.TPCConfig.enabled&&typeof(gWtId)=="undefined"&&typeof(gTempWtId)=="undefined"){
        var s = document.createElement("script");
        s.type = "text/javascript"; 
	    s.async = true; 
        s.src = "//"+this.domain+"/"+this.dcsid+"/wtid.js";
        var S = document.getElementsByTagName("script")[0]; 
        S.parentNode.insertBefore(s, S);
        return s;
	}
	else return null;
};
WebTrends.prototype.dcsGetCookie=function(name){
	var cookies=document.cookie.split("; ");
	var cmatch=[];
	var idx=0;
	var i=0;
	var namelen=name.length;
	var clen=cookies.length;
	for (i=0;i<clen;i++){
		var c=cookies[i];
		if ((c.substring(0,namelen+1))==(name+"=")){
			cmatch[idx++]=c;
		}
	}
	var cmatchCount=cmatch.length;
	if (cmatchCount>0){
		idx=0;
		if ((cmatchCount>1)&&(name==this.FPCConfig.name)){
			var dLatest=new Date(0);
			for (i=0;i<cmatchCount;i++){
				var lv=parseInt(this.dcsGetCrumb(cmatch[i],"lv"));
				var dLst=new Date(lv);
				if (dLst>dLatest){
					dLatest.setTime(dLst.getTime());
					idx=i;
				}
			}
		}
		return unescape(cmatch[idx].substring(namelen+1));
	}
	else{
		return null;
	}
};
WebTrends.prototype.dcsGetCrumb=function(cval,crumb,sep){
	var aCookie=cval.split(sep||":");
	for (var i=0;i<aCookie.length;i++){
		var aCrumb=aCookie[i].split("=");
		if (crumb==aCrumb[0]){
			return aCrumb[1];
		}
	}
	return null;
};
WebTrends.prototype.dcsGetIdCrumb=function(cval,crumb){
	var id=cval.substring(0,cval.indexOf(":lv="));
	var aCrumb=id.split("=");
	for (var i=0;i<aCrumb.length;i++){
		if (crumb==aCrumb[0]){
			return aCrumb[1];
		}
	}
	return null;
};
WebTrends.prototype.dcsIsFpcSet=function(name,id,lv,ss){
	var c=this.dcsGetCookie(name);
	if (c){
		return ((id==this.dcsGetIdCrumb(c,"id"))&&(lv==this.dcsGetCrumb(c,"lv"))&&(ss==this.dcsGetCrumb(c,"ss")))?0:3;
	}
	return 2;
};
WebTrends.prototype.dcsDeleteCookie=function(name, path, domain) {	
	var cDelete = name + "=";
	cDelete += "; expires=expires=Thu, 01 Jan 1970 00:00:01 GMT";
	cDelete += "; path=" + path;
	cDelete += (domain) ? ";domain="+domain : "";		
	document.cookie = cDelete;
};
WebTrends.prototype.dcsFPC=function(){
	if (document.cookie.indexOf("WTLOPTOUT=")!=-1){
		return;
	}

	if (!this.FPCConfig.enabled) {
		this.dcsDeleteCookie(this.FPCConfig.name, "/", this.FPCConfig.domain);
		return;
	}

	var WT=this.WT;
	var name=this.FPCConfig.name;
	var dCur=new Date();
	var adj=(dCur.getTimezoneOffset()*60000)+(this.timezone*3600000);
	dCur.setTime(dCur.getTime()+adj);
	var dSes=new Date(dCur.getTime());
	var fs,pn,vn;
	WT.co_f=WT.vtid=WT.vtvs=WT.vt_f=WT.vt_f_a=WT.vt_f_s=WT.vt_f_d=WT.vt_f_tlh=WT.vt_f_tlv="";
	if (document.cookie.indexOf(name+"=")==-1){
		if ((typeof(gWtId)!="undefined")&&(gWtId!="")){
			WT.co_f=gWtId;
		}
		else if ((typeof(gTempWtId)!="undefined")&&(gTempWtId!="")){
			WT.co_f=gTempWtId;
			WT.vt_f="1";
		}
		else{
			WT.co_f="2";
			var curt=dCur.getTime().toString();
			for (var i=2;i<=(32-curt.length);i++){
				WT.co_f+=Math.floor(Math.random()*16.0).toString(16);
			}
			WT.co_f+=curt;
			WT.vt_f="1";
		}
		if (typeof(gWtAccountRollup)=="undefined"){
			WT.vt_f_a="1";
		}
		WT.vt_f_s=WT.vt_f_d="1";
		WT.vt_f_tlh=WT.vt_f_tlv="0";
		pn=vn=1;
	}
	else{
		var c=this.dcsGetCookie(name);
		var id=this.dcsGetIdCrumb(c,"id");
		var lv=parseInt(this.dcsGetCrumb(c,"lv"));
		var ss=parseInt(this.dcsGetCrumb(c,"ss"));
	    	fs=this.dcsGetCrumb(c,"fs");pn = parseInt(this.dcsGetCrumb(c,"pn"));vn = parseInt(this.dcsGetCrumb(c,"vn"));pn=pn?(WT.dl!="0"?pn:pn+1):1;vn=vn?vn:1;
		if ((id==null)||(id=="null")||isNaN(lv)||isNaN(ss)){
			return;
		}
		WT.co_f=id;
		var dLst=new Date(lv);
		WT.vt_f_tlh=Math.floor((dLst.getTime()-adj)/1000);
		dSes.setTime(ss);
		if ((dCur.getTime()>(dLst.getTime()+1800000))||(dCur.getTime()>(dSes.getTime()+28800000))){
			WT.vt_f_tlv=Math.floor((dSes.getTime()-adj)/1000);
			dSes.setTime(dCur.getTime());
			WT.vt_f_s="1";
			vn++;pn=(WT.dl!="0")?0:1;
		}
		if ((dCur.getDay()!=dLst.getDay())||(dCur.getMonth()!=dLst.getMonth())||(dCur.getYear()!=dLst.getYear())){
			WT.vt_f_d="1";
		}
	}
	WT.co_f=escape(WT.co_f);
	WT.vtid=(typeof(this.vtid)=="undefined")?WT.co_f:(this.vtid||"");
	WT.vtvs=(dSes.getTime()-adj).toString();
	var expiry= (this.FPCConfig.expires) ? "; expires="+ new Date(new Date().getTime() + (this.FPCConfig.expires)).toGMTString():"";
	var cur=dCur.getTime().toString();
	var ses=dSes.getTime().toString();
	fs=fs?fs:cur;WT.pv_num=pn;WT.vt_num=vn;
	document.cookie=name+"="+"id="+WT.co_f+":lv="+cur+":ss="+ses+":fs="+fs+":pn="+pn.toString()+":vn="+vn.toString()+expiry+"; path=/"+(((this.FPCConfig.domain!=""))?("; domain="+this.FPCConfig.domain):(""));
	var rc=this.dcsIsFpcSet(name,WT.co_f,cur,ses);
	if (rc!=0){
		WT.co_f=WT.vtvs=WT.vt_f_s=WT.vt_f_d=WT.vt_f_tlh=WT.vt_f_tlv="";
		if (typeof(this.vtid)=="undefined"){
			WT.vtid="";
		}
		WT.vt_f=WT.vt_f_a=rc;
    }
};
WebTrends.prototype.dcsMultiTrack=function(){
	var args=dcsMultiTrack.arguments?dcsMultiTrack.arguments:arguments;
	if (args.length%2==0){
		this.dcsSetProps(args);
		var dCurrent=new Date();
		this.WT.dat =  dCurrent.getTime() - this.DCS.dcsdat;
		this.DCS.dcsdat=dCurrent.getTime();
		this.dcsFPC();
		this.dcsTag();
	}
};
WebTrends.prototype.dcsSetProps=function(args){
	for (var i=0;i<args.length;i+=2){
		if (args[i].indexOf('WT.')==0){
			this.WT[args[i].substring(3)]=args[i+1];
		}
		else if (args[i].indexOf('DCS.')==0){
			this.DCS[args[i].substring(4)]=args[i+1];
		}
		else if (args[i].indexOf('DCSext.')==0){
			this.DCSext[args[i].substring(7)]=args[i+1];
		}
	}
};
WebTrends.prototype.dcsAdv=function(){
	this.dcsFPC();
};
WebTrends.prototype.dcsVar=function(){
	var dCurrent=new Date();
	var WT=this.WT;
	var DCS=this.DCS;
	WT.tz=parseInt(dCurrent.getTimezoneOffset()/60*-1)||"0";
	WT.bh=dCurrent.getHours()||"0";
	WT.ul=navigator.appName=="Netscape"?navigator.language:navigator.userLanguage;
	if (typeof(screen)=="object"){
		WT.cd=navigator.appName=="Netscape"?screen.pixelDepth:screen.colorDepth;
		WT.sr=screen.width+"x"+screen.height;
	}
	if (typeof(navigator.javaEnabled())=="boolean"){
		WT.jo=navigator.javaEnabled()?"Yes":"No";
	}
	if (document.title){
		if (window.RegExp){
			var tire=new RegExp("^"+window.location.protocol+"//"+window.location.hostname+"\\s-\\s");
			WT.ti=document.title.replace(tire,"");
		}
		else{
			WT.ti=document.title;
		}
	}
	WT.js="Yes";
	WT.jv=(function(){
		var agt=navigator.userAgent.toLowerCase();
		var major=parseInt(navigator.appVersion);
		var mac=(agt.indexOf("mac")!=-1);
		var ff=(agt.indexOf("firefox")!=-1);
		var ff0=(agt.indexOf("firefox/0.")!=-1);
		var ff10=(agt.indexOf("firefox/1.0")!=-1);
		var ff15=(agt.indexOf("firefox/1.5")!=-1);
		var ff20=(agt.indexOf("firefox/2.0")!=-1);
		var ff3up=(ff&&!ff0&&!ff10&!ff15&!ff20);
		var nn=(!ff&&(agt.indexOf("mozilla")!=-1)&&(agt.indexOf("compatible")==-1));
		var nn4=(nn&&(major==4));
		var nn6up=(nn&&(major>=5));
		var ie=((agt.indexOf("msie")!=-1)&&(agt.indexOf("opera")==-1));
		var ie4=(ie&&(major==4)&&(agt.indexOf("msie 4")!=-1));
		var ie5up=(ie&&!ie4);
		var op=(agt.indexOf("opera")!=-1);
		var op5=(agt.indexOf("opera 5")!=-1||agt.indexOf("opera/5")!=-1);
		var op6=(agt.indexOf("opera 6")!=-1||agt.indexOf("opera/6")!=-1);
		var op7up=(op&&!op5&&!op6);
		var jv="1.1";
		if (ff3up){
			jv="1.8";
		}
		else if (ff20){
			jv="1.7";
		}
		else if (ff15){
			jv="1.6";
		}
		else if (ff0||ff10||nn6up||op7up){
			jv="1.5";
		}
		else if ((mac&&ie5up)||op6){
			jv="1.4";
		}
		else if (ie5up||nn4||op5){
			jv="1.3";
		}
		else if (ie4){
			jv="1.2";
		}
		return jv;
	})();
	WT.ct="unknown";
	if (document.body&&document.body.addBehavior){
		try{
			document.body.addBehavior("#default#clientCaps");
			WT.ct=document.body.connectionType||"unknown";
			document.body.addBehavior("#default#homePage");
			WT.hp=document.body.isHomePage(location.href)?"1":"0";
		}
		catch(e){
		}
	}
	if (document.all){
		WT.bs=document.body?document.body.offsetWidth+"x"+document.body.offsetHeight:"unknown";
	}
	else{
		WT.bs=window.innerWidth+"x"+window.innerHeight;
	}
	WT.fv=(function(){
		var i,flash;
		if (window.ActiveXObject){
			for(i=15;i>0;i--){
				try{
					flash=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+i);
					return i+".0";
				}
				catch(e){
				}
			}
		}
		else if (navigator.plugins&&navigator.plugins.length){
			for (i=0;i<navigator.plugins.length;i++){
				if (navigator.plugins[i].name.indexOf('Shockwave Flash')!=-1){
					return navigator.plugins[i].description.split(" ")[2];
				}
			}
		}
		return "Not enabled";
	})();
	WT.slv=(function(){
		var slv="Not enabled";
		try{     
			if (navigator.userAgent.indexOf('MSIE')!=-1){
				var sli = new ActiveXObject('AgControl.AgControl');
				if (sli){
					slv="Unknown";
				}
			}
			else if (navigator.plugins["Silverlight Plug-In"]){
				slv="Unknown";
			}
		}
		catch(e){
		}
		if (slv!="Not enabled"){
			var i,m,M,F;
			if ((typeof(Silverlight)=="object")&&(typeof(Silverlight.isInstalled)=="function")){
				for(i=9;i>0;i--){
					M=i;
					if (Silverlight.isInstalled(M+".0")){
							break;
					}
					if (slv==M){
						break;
					}
				}
				for (m=9;m>=0;m--){
					F=M+"."+m;
					if (Silverlight.isInstalled(F)){
						slv=F;
						break;
					}
					if (slv==F){
						break;
					}
				}
			}
		}
		return slv;
	})();
	if (this.i18n){
		if (typeof(document.defaultCharset)=="string"){
			WT.le=document.defaultCharset;
		} 
		else if (typeof(document.characterSet)=="string"){
			WT.le=document.characterSet;
		}
		else{
			WT.le="unknown";
		}
	}
	WT.sp=this.splitvalue;
	WT.dl="0";
	WT.ssl=(window.location.protocol.indexOf('https:')==0)?"1":"0";
	DCS.dcsdat=dCurrent.getTime();
	DCS.dcssip=window.location.hostname;
	DCS.dcsuri=dcsStr(window.location.pathname,250);
	WT.es=DCS.dcssip+DCS.dcsuri;
	if (window.location.search){
		DCS.dcsqry=window.location.search;
	}
	if (DCS.dcsqry){
		var dcsqry=DCS.dcsqry.toLowerCase();
		var params=this.paidsearchparams.length?this.paidsearchparams.toLowerCase().split(","):[];
		for (var i=0;i<params.length;i++){
			if (dcsqry.indexOf(params[i]+"=")!=-1){
				WT.srch="1";
				break;
			}
		}
	}
	if(this.qry["referer"]){
		DCS.dcsref=dcsSubRef(dcsTrim(this.qry["referer"].replace("https","http")));
	}
	else if ((window.document.referrer!="")&&(window.document.referrer!="-")){
		if (!(navigator.appName=="Microsoft Internet Explorer"&&parseInt(navigator.appVersion)<4)){
			DCS.dcsref=dcsSubRef(dcsTrim(window.document.referrer.replace("https","http")));
		}
	}

	DCS["dcscfg"] = this.TPCConfig.cfgType;
	
	var p=window.performance;
	if(p&&p.timing)
	{
	    WT.dat = dCurrent-p.timing.connectStart
	}
};
WebTrends.prototype.dcsEscape=function(S, REL){
	if (REL!=""){
		S=S.toString();
		for (var R in REL){
 			if (REL[R] instanceof RegExp){
				S=S.replace(REL[R],R);
 			}
		}
		S = dcsStr(S,this.max);	
		return S;
	}
	else{
		var t=dcsStr(escape(S),this.max);
		return t;
	}
};
WebTrends.prototype.dcsA=function(N,V){
	if (this.i18n&&(this.exre!="")&&!this.exre.test(N)){
		if (N=="dcsqry"){
			var newV="";
			var params=V.substring(1).split("&");
			for (var i=0;i<params.length;i++){
				var pair=params[i];
				var pos=pair.indexOf("=");
				if (pos!=-1){
					var key=pair.substring(0,pos);
					var val=pair.substring(pos+1);
					if (i!=0){
						newV+="&";
					}
					newV+=key+"="+this.dcsCode(val);
				}
			}
			V=V.substring(0,1)+newV;
		}
		else{
			V=this.dcsCode(dcsTrim(V));
		}
	}
	return "&"+N+"="+this.dcsEscape(V, this.re);
};
WebTrends.prototype.dcsEncode=function(S){
	return (typeof(encodeURIComponent)=="function")?encodeURIComponent(S):escape(S);
};
WebTrends.prototype.dcsCode=function(S){
    if(/.*[\u0391-\uFFE5]+.*$/.test(S))
        return this.dcsEncode(S);
    else return S;
};
WebTrends.prototype.dcsCreateImage=function(dcsSrc){
	if (document.images){
		this.images[this.index]=new Image();
		this.images[this.index].src=dcsSrc;
		this.index++;
	}
};
WebTrends.prototype.dcsMeta=function(){
	var elems;
	if (document.documentElement){
		elems=document.getElementsByTagName("meta");
	}
	else if (document.all){
		elems=document.all.tags("meta");
	}
	if (typeof(elems)!="undefined"){
		var length=elems.length;
		for (var i=0;i<length;i++){
			var name=elems.item(i).name;
			var content=elems.item(i).content;
			var equiv=elems.item(i).httpEquiv;
			if (name.length>0){
				if (name.toUpperCase().indexOf("WT.")==0){
					this.WT[name.substring(3)]=content;
				}
				else if (name.toUpperCase().indexOf("DCSEXT.")==0){
					this.DCSext[name.substring(7)]=content;
				}
				else if (name.toUpperCase().indexOf("DCS.")==0){
					this.DCS[name.substring(4)]=content;
				}
			}
		}
	}
};
WebTrends.prototype.dcsTag=function(){
	if (document.cookie.indexOf("WTLOPTOUT=")!=-1){
		return;
	}
	var WT=this.WT;
	var DCS=this.DCS;
	var DCSext=this.DCSext;
	var i18n=this.i18n;
	
	var P="http"+(window.location.protocol.indexOf('https:')==0?'s':'')+"://"+this.domain+(this.dcsid==""?'':'/'+this.dcsid)+"/dcs.gif?";
//	if (i18n){
//		WT.dep="";
//	}
	for (var N in DCS){
 		if (DCS[N]&&(typeof DCS[N]!="function")){
			P+=this.dcsA(N,DCS[N]);
		}
	}
	for (N in WT){
		if (WT[N]&&(typeof WT[N]!="function")){
			P+=this.dcsA("WT."+N,WT[N]);
		}
	}
	for (N in DCSext){
		if (DCSext[N]&&(typeof DCSext[N]!="function")){
//			if (i18n){
//				WT.dep=(WT.dep.length==0)?N:(WT.dep+";"+N);
//			}
			P+=this.dcsA(N,DCSext[N]);
		}
	}
//	if (i18n&&(WT.dep.length>0)){
//		P+=this.dcsA("WT.dep",WT.dep);
//	}
	if (P.length>2048&&navigator.userAgent.indexOf('MSIE')>=0){
		this.max=this.max/2;
		WT.tu=1;
		this.dcsTag();return;
		//P=P.substring(0,2040)+"&WT.tu=1";
	}
	this.dcsCreateImage(P);
	this.WT.ad="";
};
WebTrends.prototype.dcsCollect=function(){
    if (this.enabled){
        this.dcsGetqry();
        this.dcsVar();
        this.dcsMeta();
        this.dcsAdv();
        this.dcsLoad();
        if (typeof(this.dcsCustom)=="function"){
		    this.dcsCustom();
        }
        this.dcsTag();
    }
};

function dcsMultiTrack(){
	if (typeof(_tag)!="undefined"){
		return(_tag.dcsMultiTrack());
	}
};

//Add by Zhengzy
WebTrends.prototype.dcsGetqry = function(){
    var s = window.location.search;
    if(s.length>1)
    {
        var o = s.substring(1).split('&');
        var item;
        for(var i=0;i<o.length;i++)
        {
            item = o[i].split('=');
            this.qry[item[0].toLowerCase()] = item[1];  
            }
    }
};
WebTrends.prototype.dcsv = function (o,n){
    var l=n.toLowerCase();var a=o.attributes;
    if(a&&a[n]) 
        return a[n].nodeValue||a[n].value;
    else if(a&&a[l]) 
        return a[l].nodeValue||a[l].value;
    else
        return null
};
WebTrends.prototype.pa_sdcajax = function (){
    this.DCSext.wt_click=null;
    this.WT = {}; 
    this.WT.pa_ajax=1;
    this.dcsGetqry();
    this.dcsVar();
    this.WT.dl=21;
    this.dcsMeta();
    this.dcsAdv();
    this.dcsLoad();
    if (typeof(this.dcsCustom)=="function"){
		this.dcsCustom();
    }
    var l = 2;
    var b = pa_sdcajax.arguments ? pa_sdcajax.arguments : arguments;
    if(b[2]!=null&&typeof(b[2])=="boolean") l = 3;
    
    if ((b.length%2==0&&l==2) || (b.length%3==0&&l==3)){
	for (var i=0;i<b.length;i+=l){
		if (b[i].indexOf('WT.')==0){
			this.WT[b[i].substring(3)]=b[i+1];
		}
		else if (b[i].indexOf('DCS.')==0){
			this.DCS[b[i].substring(4)]=b[i+1];
		}
		else if (b[i].indexOf('DCSext.')==0){
			this.DCSext[b[i].substring(7)]=b[i+1]
		}
	}
    }
    this.dcsTag();
    this.WT.pa_ajax=this.WT.dl=null;
    if (l==3){
        for (var i=0;i<b.length;i+=3){
            if (b[i].indexOf('WT.')==0){
                if(!b[i+2]) this.WT[b[i].substring(3)]=null;
            }
            else if (b[i].indexOf('DCS.')==0){
                if(!b[i+2]) this.DCS[b[i].substring(4)]=null;
            }
            else if (b[i].indexOf('DCSext.')==0){
                if(!b[i+2]) this.DCSext[b[i].substring(7)]=null;
            }
        }
    }
    this.dcsComplete();
};
WebTrends.prototype.autoclick = function () {
    var o = new Array();
    var d = document;
    
    if (d.all) {
        o = d.getElementsByTagName("INPUT");
        var i = 0;
        for (i = 0; i < o.length; i++) {
            if (o[i].type == "password" || o[i].type == "text") {
                o[i].detachEvent('onfocus', this.dcse);
                o[i].attachEvent('onfocus', this.dcse);
                o[i].detachEvent('onblur', this.dcse);
                o[i].attachEvent('onblur', this.dcse)
            }
        }
        o = d.getElementsByTagName("TEXTAREA");
        for (var i = 0; i < o.length; i++) {
            o[i].detachEvent('onfocus', this.dcse);
            o[i].attachEvent('onfocus', this.dcse);
            o[i].detachEvent('onblur', this.dcse);
            o[i].attachEvent('onblur', this.dcse)
        }
        o = d.getElementsByTagName("SELECT");
        for (var i=0;i<o.length;i++){
            o[i].detachEvent('onchange', this.dcse);
            o[i].attachEvent('onchange', this.dcse)
        }

        d.body.detachEvent('onclick', this.dcse);
        d.body.attachEvent('onclick', this.dcse)
    }

    else{
        o = d.getElementsByTagName("INPUT");
        var i = 0;
        for (i = 0; i < o.length; i++) {
            if (o[i].type == "password" || o[i].type == "text") {
                o[i].addEventListener('focus', this.dcse, false);
                o[i].addEventListener('blur', this.dcse, false)
            }
        }
        o = d.getElementsByTagName("TEXTAREA");
        for (var i = 0; i < o.length; i++){
            o[i].addEventListener('focus', this.dcse, false);
                o[i].addEventListener('blur', this.dcse, false) 
                }
        o=d.getElementsByTagName("SELECT");
        for (var i=0;i<o.length;i++)
            o[i].addEventListener('change', this.dcse,false);

        d.body.addEventListener('click', this.dcse, false)
    }
};
WebTrends.prototype.dcse = function(evt) {
if(typeof(_tag.dcsE)=="function") {_tag.dcsE(evt);return;}
var t;
if (document.all) t = window.event.srcElement;
else t = evt.target;

var a=_tag.WT;
a.tsp=a.ttp=a.ti=a.obj=a.inputval=a.area=a.obj=a.texturl=a.textarea=a.textSerial=a.si_entry=a.pn_entry=null;

if(!_tag.dcsv(t, "otitle"))
{
    var p=t;
    for(var i=0;i<4;i++)
    {
        if(p.parentNode)
        {
            p = p.parentNode;
            if (p && _tag.dcsv(p, "otitle")) {
                t = p;
                break;
            }
        }
        else break;
    }
}

var tn = t.tagName.toLowerCase();
if(tn=="select"&&evt.type!="change") return;
if (t != _tag.lt) _tag.lt = t;
else if (t == _tag.lt && evt.type == "focus") return;
if (!_tag.dcsv(t, "otitle")) return;

a.ti = _tag.dcsv(t, "otitle");
a.obj = _tag.dcsv(t, "otype");
a.area = _tag.dcsv(t, "oarea");

if(a.obj=="entry")
{
    a.si_entry=a.ti;
}
else if(a.obj=="nav")
{
    a.pn_entry=a.ti;
}
else if(tn=="a"&&a.obj&&a.obj.indexOf("adtext")>-1)
{
    a.texturl = t.href;
    a.textarea = _tag.dcsv(t,"adtextArea");   
    a.ti = t.innerText||t.textContent||a.ti;
    var o = document.getElementsByTagName("A");    
    var i,j;
    for(i=0,j=0;i<o.length;i++)
    {
        if(_tag.dcsv(o[i],"adtextArea")==a.textarea) 
        {
            j++;
            if(o[i] == t)
            {   
                a.textSerial = j;break;
            }
        }
    }
}
else if(t.type=="text"||tn=="textarea") {
    if(evt.type == "click") return;    
    else if(evt.type == "blur")
    {
        if(t.value=='') {_tag.lt=t;_tag.ltv=t.value;return}
        if(t==_tag.lt&&t.value==_tag.ltv) {return}
        a.inputval = t.value;
        a.obj = "input"
    }
}
else
if(t.type=="radio"){
    a.inputval=t.value
}
else
if(t.type=="checkbox"){
    a.inputval=t.checked?"1":"0"
}
else
if(tn=="select"){
    a.inputval=t.options[t.selectedIndex].text
}
 
var url=window.location.pathname+"\/"+evt.type+".event";
a.pageurl="http://"+window.location.hostname+window.location.pathname;
a.pagetitle = document.title;
_tag.lt=t;_tag.ltv=t.value;
_tag.dcsMultiTrack('DCS.dcsuri',url,'WT.dl',25,'DCSext.wt_click','page');

};
WebTrends.prototype.dcsisS = function()
{
    if((this.DCS.dcsref)==null) return false;
    var u = this.DCS.dcsref.toLowerCase();
    if(dcsExec(u,".baidu.com|.google.|.360.cn|.so.com|.haosou.com|.sm.cn|.yahoo.|.soso.com|.114search.|.aol.com|.bing.com|.sogou.com|.live.com")&&dcsExec(u,".baidu.com/|[?&]keyword=|[?&]key_word=|[?&]word=|[?&]wd=|[?&]q=|[?&]p=|[?&]w=|[?&]query=")) return true;
    else return false;
};
WebTrends.prototype.dcsGetRef = function()
{
    var d = this.WT;
    var m = this.qry["wt.mc_id"];
    d.pa_dom = this.DCS.dcssip;
    var r = this.DCS.dcsref;
    if(r){
        var a = r.split("/");
        if(a.length>2) r = a[2];
    }
    
    if((r==null||r==""||r.indexOf(this.FPCConfig.domain)!=-1||r.indexOf(".pingan.com")!=-1||r.indexOf(".4008000000.com")!=-1)&&(m==null||m==""))
    {
        d.pa_ref="direct";
    }
    else if(this.dcsisS())
    {
        if(m&&m!="")
            d.pa_ref="sem";
        else
            d.pa_ref="seo";
    }
    else if(m&&m!=""){
        var a=new Array("sms","edm");
        for(var i=0;i<a.length;i++)
        {
            if(m.indexOf(a[i])>-1) {d.pa_ref = a[i];break}
        }
        if(!d.pa_ref) d.pa_ref="other_campaign";
    }
    else
        d.pa_ref="freelink";

};
WebTrends.prototype.dcsgetPaCookie = function() {
	var a = this.WT;
    if(window.location.host.toLowerCase().indexOf("pingan.com")!=-1)
    {
      var gUserIdName = "PA18RUM_USER_IDENTIFIER";
      var paUserId = null;
      pUserId = this.dcsGetCookie(gUserIdName);
      if (pUserId&&pUserId!="null") { 
          var i = pUserId.indexOf("@");
          if(i!=-1)
            a.pa_tbuid = pUserId.substring(0,i);
          else
            a.pa_tbuid = pUserId;
          a.pa_tnuser =  '1';
      }
      else 
        a.pa_tnuser = '0';
      } 
};
WebTrends.prototype.dcsTP = function () {
    if (document.cookie.indexOf("WTLOPTOUT=") != -1) {
        return;
    }
    var name = "WT_DC";

    if (this.dcsGetCookie(name)) {
        this.WT.ttp = "1";
    }
    if (this.dcsGetMeta("SmartView_Page") == "1") {
        this.WT.tsp = "1";
        document.cookie = name + "=tsp=1; path=/; domain="+this.FPCConfig.domain;
    } else {
        this.dcsDeleteCookie(name,"/",this.FPCConfig.domain);
    }
};
WebTrends.prototype.dcsGetMeta = function (n) {
    var s;
    if (document.all) {
        s = document.all.tags("meta");
    } else if (document.documentElement) {
        s = document.getElementsByTagName("meta");
    }
    if (typeof (s) != "undefined") {
        for (var i = 0; i < s.length; i++) {
            var m = s.item(i);
            if (m.name && (m.name.indexOf(n) == 0)) {
                return m.content;
                break;
            }
        }
    }
    return null;
};
WebTrends.prototype.dcssmac = function () {
    if (this.dcsGetMeta("SmartView_Page") == "1") {
        var o = new Array();
        if (window.attachEvent) {
            o = document.getElementsByTagName("A");
            for (var i = 0; i < o.length; i++) {
                if (o[i].href.indexOf("http") == 0) {
                    o[i].detachEvent('onclick', this.dcssme);
                    o[i].attachEvent('onclick', this.dcssme);
                }
            }
        }
        if (window.addEventListener) {
            o = document.getElementsByTagName("A");
            for (var i = 0; i < o.length; i++) {
                if (o[i].href.indexOf("http") == 0) o[i].addEventListener('click', this.dcssme, false);
            }
        }
    }
};
WebTrends.prototype.dcssme = function () {
    var t = _tag.dcsid;
    _tag.dcsid="dcssxwu8kj0ujdxxt6w5gxvjy_3j8z";
    _tag.pa_sdcajax('WT.dl','22',false,'WT.pa_smlink','1',false,'WT.pa_ajax',null,false);
    _tag.dcsid=t
};
WebTrends.prototype.dcspaAutoContent = function(){
	var a = this.WT;
	var r = this.DCS.dcsref;
    var c = window.location.pathname.split("/");
    if(c[2]&&!a.pa_cgn) a.pa_cgn = c[1];
    if(c[3]&&!a.pa_cgs) a.pa_cgs = c[2];
    
    if(r&&r.indexOf(this.FPCConfig.domain)>0)
    {
	    c = r.split("?")[0].split("/");
	    if(c[4]) a.re_cgn = c[3];
	    if(c[5]) a.re_cgs = c[4];
    }
};
WebTrends.prototype.dcsLoad = function(){
this.dcsgetPaCookie();
this.dcspaAutoContent();
this.dcsGetRef();
this.WT.hash = window.location.hash.substring(1).split('?')[0];
this.dcsTP();
};
WebTrends.prototype.dcsComplete = function(){
this.autoclick();
this.dcssmac();
};
function pa_sdcajax() {
    if (typeof(_tag) != "undefined") {
        return (_tag.pa_sdcajax())
    }
};
var _tag = new WebTrends();
try{_tag.autoclick()}catch(e){};
document.onreadystatechange=function(){"complete"==document.readyState&&_tag.dcsComplete()};
var WT = _tag.WT;
var DCS = _tag.DCS;
var DCSext = _tag.DCSext;

function dcsTrim(s)
{
    var str = new String(s);
    return str.replace(/(^\s*)|(\s*$)|(\#$)/g, '');
}
function dcsExec(s, r)
{
    var reg=new RegExp(r);
    return reg.exec(s);
}
function dcsStr(a,b,c){return a.length<=b?a:c?a.substring(a.length-b):a.substring(0,b)}
function dcsSubRef(f){
	f = f.toLowerCase();
	f=f.replace("wd=&","wd=null&").replace("word=&","word=null&");
	if(dcsExec(f,"baidu.com|google.com|so.com|haosou.com|sogou.com"))
 		while(f.length>800) 
				{
					var r = f.split("?");
					var qry,objs,item,maxlen=0,str="";
					if(r.length>1)
					{                
						 qry = r[1];
						 objs = qry.split("&");
						if(objs.length>1)
						{
							for(var i=0;i<objs.length;i++)
								{
									if(objs[i].indexOf("=")==-1)
									 {
											if(qry.indexOf(objs[i]+"&")!=-1)										
															f = f.replace(objs[i]+"&","");																									
											else 	f = f.replace(objs[i],"");
											str = "";
											break;	
										}
									else
									{
										item = objs[i].split("=")[0];
										val = objs[i].split("=")[1];
										if(typeof(val)!="undefined"&&objs[i].length>maxlen) 
											{
												maxlen = objs[i].length;
												if(f.indexOf("?"+item)!=-1)
                                str = item.toString()+"="+val.toString();
               	        else str = "&"+item.toString()+"="+val.toString();                	                  
											}
									}
							  }
								f = f.replace(str,""); 
						}
						else break;											
				  }
        else break;							
			}
   return f;
}
