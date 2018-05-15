var Wpfc_Lazyload = {
	sources: [],
	loaded_index:[],
	init: function(){
		this.set_source(document.getElementsByTagName("img"));
		this.set_source(document.getElementsByTagName("iframe"));

		window.addEventListener('scroll', function(){Wpfc_Lazyload.load_sources();});
		window.addEventListener('resize', function(){Wpfc_Lazyload.load_sources();});
		window.addEventListener('click', function(){Wpfc_Lazyload.load_sources();});
	},
	c: function(e, pageload){
		var winH = document.documentElement.clientHeight || body.clientHeight;
		var number = pageload ? 0 : 800;
		var elemRect = e.getBoundingClientRect();
		var top = 0;
		var parent = e.parentNode;
		var parentRect = parent.getBoundingClientRect();

		if(elemRect.x == 0 && elemRect.y == 0){
			for (var i = 0; i < 10; i++) {
				if(parent){
					if(parentRect.x == 0 && parentRect.y == 0){
						parent = parent.parentNode;
						parentRect = parent.getBoundingClientRect();
					}else{
						top = parentRect.top;
						break;
					}
				}
			};
		}else{
			top = elemRect.top;
		}


		if(winH - top + number > 0){
			return true;
		}

		return false;
	},
	r: function(e, pageload){
		var self = this;
		var originalsrc,originalsrcset;

		try{

			if(self.c(e, pageload)){
				originalsrc = e.getAttribute("wpfc-data-original-src");
				originalsrcset = e.getAttribute("wpfc-data-original-srcset");

				if(originalsrc || originalsrcset){
					if(originalsrc){
						e.setAttribute('src', originalsrc);
					}

					if(originalsrcset){
						e.setAttribute('srcset', originalsrcset);
					}

					e.removeAttribute("wpfc-data-original-src");
					e.removeAttribute("onload");
				}
			}

		}catch(error){
			console.log(error);
			console.log("==>", e);
		}
	},
	set_source: function(arr){
		if(arr.length > 0){
			var self = this;
			[].forEach.call(arr, function(e, index) {
				self.sources.push(e);
			});
		}
	},
	load_sources: function(){
		var self = this;

		[].forEach.call(self.sources, function(e, index) {
			self.r(e, false);
		});
	}
};

document.addEventListener('DOMContentLoaded',function(){
	wpfcinit();
});

function wpfcinit(){
	Wpfc_Lazyload.init();
}