var DOM = KISSY.DOM ,Event = KISSY.Event;
var btn_debug = DOM.query("#think_page_trace_open")[0];
KISSY.Event.on(btn_debug,"click",function(e){
	var debug_panel = KISSY.DOM.query("#think_page_trace")[0];
	var debug_tab = KISSY.DOM.query("#think_page_trace_tab")[0];
	if(debug_tab.style.display == "block"){
		debug_tab.style.display = "none";
		debug_panel.style.display = "none";
	}else{
		debug_tab.style.display = "block";
		debug_panel.style.display = "block";
	}
});



//KISSY.use('event,dom',function(KISSY,Event,DOM){

	var debug_title = DOM.query(".debug_title");
	var debug_info = DOM.query(".debug_info");


	Event.on(debug_title, 'click', function(e){
		var i = 0;
		for(i in debug_title){
			debug_info[i].style.display = "none";
		}
		for(i in debug_title){
			if(debug_title[i] == this){
				break;
			}
		}
		console.log(i);
		debug_info[i].style.display = "block";
	});
//});