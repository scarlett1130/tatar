/* Dynamic images, change class */
// MooTools
window
    .addEvent('domready', function()
    {

	    $$('*.dynamic_img').addEvents(
	    {
	        'mouseenter': function()
	        {
		        this.addClass('over');
	        },
	        'mouseleave': function()
	        {
		        this.removeClass('over');
		        this.removeClass('clicked');
	        },
	        'mousedown': function()
	        {
		        this.removeClass('over');
		        this.addClass('clicked');
	        }
	    });

	    $$('img.villagesSwitch')
	        .addEvents(
	        {
		        'mousedown': function()
		        {
			        var tbody = this.getParent('thead').getNext('tbody');
			        tbody.toggleClass('hide');
			        if (tbody.hasClass('hide'))
			        {
				      document.cookie=("showVillages=0")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				       this.removeClass('opened');
				        this.addClass('closed');

			        }
			        else
			        {
						document.cookie=("showVillages=1")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				        this.removeClass('closed');
				        this.addClass('opened');
			        }
		        }
	        });
	    
	    $$('img.linksSwitch')
	        .addEvents(
	        {
		        'mousedown': function()
		        {
			        var tbody = this.getParent('thead').getNext('tbody');
			        tbody.toggleClass('hide');
			        if (tbody.hasClass('hide'))
			        {
					  document.cookie=("showLinks=0")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				       this.removeClass('opened');
				        this.addClass('closed');

			        }
			        else
			        {
						document.cookie=("showLinks=1")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				        this.removeClass('closed');
				        this.addClass('opened');
			        }
		        }
	        });


	    $$('img.allianceChat')
	        .addEvents(
	        {
		        'mousedown': function()
		        {
			        var tbody = this.getParent('thead').getNext('tbody');
			        tbody.toggleClass('hide');
			        if (tbody.hasClass('hide'))
			        {
					  document.cookie=("alliancchat=0")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				       this.removeClass('opened');
				        this.addClass('closed');

			        }
			        else
			        {
						document.cookie=("alliancchat=1")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				        this.removeClass('closed');
				        this.addClass('opened');
			        }
		        }
	        });

	    $$('img.guide_quiz')
	        .addEvents(
	        {
		        'mousedown': function()
		        {
			        var tbody = this.getParent('thead').getNext('tbody');
			        tbody.toggleClass('hide');
			        if (tbody.hasClass('hide'))
			        {
					  document.cookie=("guide_quiz=0")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				       this.removeClass('opened');
				        this.addClass('closed');

			        }
			        else
			        {
						document.cookie=("guide_quiz=1")+"; expires=Wed, 1 Jan 2250 00:00:00 GMT"
				        this.removeClass('closed');
				        this.addClass('opened');
			        }
		        }
	        });
    });
    
    
var BBEditor = new Class ({
	preview: null,
	textArea: null,
	id: null,

	Binds: ['fetchPreview', 'showToolbarWindow', 'insertTag', 'insertSingleTag', 'insertSmilieTag', 'hideToolbarWindow', 'showPreview', 'hidePreview'],

	/**
	 * Initialisiert den Editor
	 */
	initialize: function(textAreaId) {

		//connect elements
		this.id = textAreaId;
		this.textArea = $(textAreaId);
		this.toolbar = $(textAreaId + '_toolbar');
		this.preview = $(textAreaId + '_preview');

		//init elements
		this.preview.setStyle('display', 'none');

		//add Events
		$(textAreaId + '_previewButton').addEvent('click', this.fetchPreview);
		$(textAreaId + '_resourceButton').addEvent('click', this.showToolbarWindow);
		$(textAreaId + '_smilieButton').addEvent('click', this.showToolbarWindow);
		$(textAreaId + '_troopButton').addEvent('click', this.showToolbarWindow);
		$(textAreaId).addEvent('click', this.hideToolbarWindow);
		this.addEvent($(textAreaId + '_toolbar'), this.insertTag);
		this.addEvent($(textAreaId + '_resources'), this.insertTag);
		this.addEvent($(textAreaId + '_smilies'), this.insertTag);
		this.addEvent($(textAreaId + '_troops'), this.insertTag);
	},

	/**
	 * Fأ¼gt den klickbaren Objekten die Events hinzu
	 *
	 * @param object containerObjekt
	 * @param string callback
	 */
	addEvent: function(div, call) {
		var childen =  div.getChildren();
		for (i = 0; i < childen.length; i++) {
			if ($(childen[i]).get('bbTag')) {
				$(childen[i]).addEvent('click', call);
			}
		}
	},

	/**
	 * Fأ¼gt einen ausgewأ¤hlten Tag in die
	 * Textarea ein
	 *
	 * @param Object
	 */
	insertTag: function(Event) {
		this.hidePreview();
		var link = $(Event.target.parentNode);
		var tag = link.get('bbTag');

		switch (link.get('bbType')) {
			//double tag
			case 'd':
				this.textArea.insertAroundCursor({before: '[' + tag + ']', after: '[/' + tag + ']'});
				break;
			//smilie
			case 's':
				this.textArea.insertAtCursor(tag, false);
				break;
			//once
			case 'o':
				this.textArea.insertAtCursor('[' + link.get('bbTag') + ']', false);
				break;
		}
	},

	/**
	 * Zeigt ein Unterfenster der Toolbar
	 * an
	 *
	 * @param Object
	 */
	showToolbarWindow: function(Event) {
		var targetDiv = Event.target.parentNode;
		var window = $(this.id + '_' +  targetDiv.get('bbWin'));

		var show = true;
		if (window.getStyle('display') == 'block') {
			show = false;
		}

		this.hideToolbarWindow();

		if (show) {
			window.fade('hide').fade('in');
			window.setStyle('display', 'block');
		}
	},

	/**
	 * Versteckt die Fenster der Toolbar
	 *
	 * @param Object
	 */
	hideToolbarWindow: function() {
		var childen =  $(this.id + '_toolbarWindows').getChildren();
		for (i = 0; i < childen.length; i++) {
			$(childen[i]).setStyle('display', 'none');
		}
	},

	/**
	 * Holt die Vorschau vom Server
	 *
	 * @param Object
	 */
	fetchPreview: function(Event) {
		if (this.textArea.getStyle('display') == 'none' || this.textArea.value.length < 1) {
			this.hidePreview();
			return;
		}

		var jsonRequest = new Request.JSON({
			method: 'post',
			url: 'ajax.php?f=bb',
			data:
			{
				nl2br:	1,
				target:	this.id,
				text:	this.textArea.value
			},
			onSuccess: this.showPreview
		});
		jsonRequest.post();
	},

	/**
	 * Zeigt die Vorschau
	 *
	 * @param string textAreaId
	 */
	showPreview: function(data) {
		if (data.error == true) {
			alert(data.errorMsg);
			return;
		} else {
			this.preview.innerHTML = data.text;
			this.preview.setStyle('display','block');
			this.textArea.setStyle('display','none');
		}
	},

	/**
	 * Versteckt die Vorschau
	 *
	 * @param string textAreaId
	 */
	hidePreview: function() {
		this.preview.setStyle('display','none');
		this.textArea.setStyle('display','inline');
	}
});



var attackSysbolState = new Array();

function getAttackSymbolState(id)
{
	var state = attackSysbolState[id];

	if (!state)
	{
		state = new Object();

		var type = 0;

		var imgClass = $('markSybol_'+id).get('class');

		var color = imgClass.substr(imgClass.lastIndexOf('_')+1, 11);

		switch (color)
		{
			case 'green':
				type = 1;
				break;
			case 'yellow':
				type = 2;
				break;
			case 'red':
				type = 3;
				break;
			default:
				type = 0;
				break;
		}
		state.type = type;
		state.oldType = type;


		attackSysbolState[id] = state;
	}

	return state;
}

function drawAttackSymbol(id)
{
	var state = getAttackSymbolState(id);

	if (state.type == 4)
	{
		state.type = 0;
	}

	switch (state.type)
	{
		case 1:
			img = 'img/green.gif';
			color = 'green';
			break;
		case 2:
			img = 'img/yellow.gif';
			color = 'yellow';
			break;
		case 3:
			img = 'img/red.gif';
			color = 'red';
			break;
		default:
			img = 'img/grey.gif';
			color = 'grey';
			break;
	}
	$('markSybol_'+id).set('class', 'attack_symbol_'+color);
}

function markAttackSymbol(id)
{

	var state = getAttackSymbolState(id);
	state.type ++ ;

	drawAttackSymbol(id);

	if (state.isSaving != true)
	{
		state.isSaving = true;

		(function()
		{
			if (state.type != state.oldType)
			{
				var jsonRequest = new Request.JSON(
				{
				   method: 'post',
				   url: 'ajax.php?f=vp&id='+id+'&state='+state.type,
				   onSuccess: function(data)
				   {
						var state = getAttackSymbolState(data.id);

						state.isSaving = false;

						state.type = data.type;
						state.oldType = data.type;

						drawAttackSymbol(data.id);
				   }
				});
				jsonRequest.post();
			}
			else
			{
				state.isSaving = false;

			}

		 }).delay(1000);
	}

}




	function editvalueres() {
		
		var res1 = +(document.getElementById("nr1").value);
		var res2 = +(document.getElementById("nr2").value);
		var res3 = +(document.getElementById("nr3").value);
		var res4 = +(document.getElementById("nr4").value);
		
		var newnum = (res1+res2+res3+res4);
		
		var oldnum = (document.getElementById("noldnum").innerHTML);
		
		if ( newnum > oldnum )
		{
			var res = Math.floor(oldnum/4);
			editresvaluenow(res,res,res,res);
		}
		else
		{
			var restnum = (oldnum - newnum);
			document.getElementById("nnewnum").innerHTML = newnum+"";
			document.getElementById("nrestnum").innerHTML = restnum+"";
		}
		
	}
	
	function editresvaluenow( res1,res2,res3,res4 )
	{
		document.getElementById("nr1").value = res1+"";
		document.getElementById("nr2").value = res2+"";
		document.getElementById("nr3").value = res3+"";
		document.getElementById("nr4").value = res4+"";
		document.getElementById("nnewnum").innerHTML = ( +res1 +res2 +res3 +res4 )+"";
		document.getElementById("nrestnum").innerHTML = 0;
	}
	
	function completenpcnow() {
		
		var res1 = +(document.getElementById("nr1").value);
		var res2 = +(document.getElementById("nr2").value);
		var res3 = +(document.getElementById("nr3").value);
		var res4 = +(document.getElementById("nr4").value);
		
		var newnum = (res1+res2+res3+res4);
		
		var oldnum = (document.getElementById("noldnum").innerHTML);
		
		if ( newnum > oldnum )
		{
			var res = Math.floor(oldnum/4);
			editresvaluenow(res,res,res,res);
		}
		else
		{
			var restnum = Math.floor((oldnum - newnum)/4);
			
			var nres1 = res1+restnum;
			var nres2 = res2+restnum;
			var nres3 = res3+restnum;
			var nres4 = res4+restnum;
			
			var nnewnum = (nres1+nres2+nres3+nres4);
			
			var restnum = Math.floor((oldnum - nnewnum));
			
			editresvaluenow(nres1,nres2,nres3,nres4);
			
			document.getElementById("nnewnum").innerHTML = nnewnum+"";
			document.getElementById("nrestnum").innerHTML = restnum+"";
			
			editvalueres();
			
			document.getElementById("nr1").disabled = true;
			document.getElementById("nr2").disabled = true;
			document.getElementById("nr3").disabled = true;
			document.getElementById("nr4").disabled = true;
			
			document.getElementById("submitexncp").innerHTML = '<input type="submit" name="submitexncp" onclick="submitexncp()" value="بادل الآن" />'
			
			
			
		}
		
	}
	
	function submitexncp() {
		
		var id = document.getElementById("playerid").value;
		
		var res1 = +(document.getElementById("nr1").value);
		var res2 = +(document.getElementById("nr2").value);
		var res3 = +(document.getElementById("nr3").value);
		var res4 = +(document.getElementById("nr4").value);
		
		var newnum = (res1+res2+res3+res4);
		
		var oldnum = (document.getElementById("noldnum").innerHTML);
		
		if ( newnum > oldnum )
		{
			var res = Math.floor(oldnum/4);
			gotrader(res,res,res,res,oldnum,id);
		}
		else if ( newnum == oldnum )
		{
			gotrader(res1,res2,res3,res4,oldnum,id);
		}
		else
		{
			var restnum = (oldnum - newnum);
			gotrader((res1+restnum),res2,res3,res4,oldnum,id);
		}
		
	}