/**
 * @class Plex Is Helper Object
 */
Plex = {
	
	/**
	 * Base Urls
	 */
	Url:{
		imageInf:'/handlers/Image/db_image.php',
		base:'/',
		image:'/img/',
		css:'/css/',
		js:'/js/'
	},
	
	/**
	 * Base Settings
	 */
	Settings:{
		languageId : 1,
		startTime: (new Date()),
		cache: true
	},

	/**
	 * Samurai Helper
	 */
	S$:{
		extend:function(T,X)
		{
			var OX = T.X;
			T.X = X;
			T.X();
			T.X=OX;
		},

		empty:function()
		{
			for ( var i=0; i < arguments.length; i++ )
			{
				var parent = arguments[i];
				var kids = parent.childNodes;

				for(var j=0;j<kids.length;j++)
				parent.removeChild(kids.item(0));
			}
		},

		remove:function()
		{
			for ( var i=0; i < arguments.length; i++ )
			arguments[i].parentNode.removeChild(arguments[i]);
		}
	},

	/**
	 * COM
	 */
	Com:{
		Image:{
			getURL : function(imageId, maxW, maxH)
			{
				if(imageId==0)
				return Plex.Url.image+'Com/Image/blank.png';
				else
				{
					var img = Plex.Url.imageInf+"?id="+imageId;
					
					if(maxW)
					img += "&maxX="+maxW;
					
					if(maxH)
					img += "&maxY="+maxH;
					
					return img;
				}
			}
		}
	},

	/**
	 * Lib Is used to load dependancies
	 */
	Lib:{
		JS:{
			loaded:Array(),
			load:function(lib,onLoad)
			{
				if(Plex.Lib.JS.loaded.indexOf(lib)==-1 || !Plex.Settings.cache)
				{
					if(window.XMLHttpRequest)
					var req = new XMLHttpRequest();
					else
					var req = new ActiveXObject("Microsoft.XMLHTTP");

					req.open("GET", Plex.Url.js+lib.replace('.','/').replace('.','/').replace('.','/')+'.js?time='+Plex.Settings.startTime.getTime(),false);
					req.send(null);
					window.eval(req.responseText);
					
					
					
					Plex.Lib.JS.loaded.push(lib);
				}
			}
		},

		CSS:{
			loaded:Array(),
			load:function(lib)
			{
				if(Plex.Lib.CSS.loaded.indexOf(lib)==-1)
				{
					$('cssLib').appendText('@import "' + Plex.Url.css+lib.replace('.','/').replace('.','/').replace('.','/')+'.css?time='+Plex.Settings.startTime.getTime() + '"; ')
					Plex.Lib.CSS.loaded.push(lib);
				}
			}
		}
	},

	/**
	 * Current UI Envirement Varibles
	 */
	UI:{
		zIndex:10,
		Button:{},
		Page:{},
		Input:{},
		Widget:{}
	},
	
	/**
	 * Util CORE
	 */
	Util:{},
	
	
	/**
	 * Data CORE
	 */
	Data:{},
	
	Debug: function(what){
		console.log(what);
	},

	/**
	 * Empty Handler
	 */
	doNothing:function(){}
}
