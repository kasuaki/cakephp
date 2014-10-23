
function obj_dump(obj) {
	var txt = '';
	for (var one in obj){
		txt += one + "=" + obj[one] + "\n";
	}
	alert(txt);
}

var User = Backbone.Model.extend({

	defaults: {
		"id" : "",
		"username" : "",
		"password" : "",
		"role" : "",
		"created" : "",
		"modified" : "",
	}
});

  var UserList = Backbone.Collection.extend({

    // Reference to this collection's model.
    model: User,

    // Save all of the User items under the `"users-backbone"` namespace.
//    localStorage: new Backbone.LocalStorage("users-backbone"),

    url: '/users',

    parse: function(response) {
      return response.users != undefined ? response.users : response;
    },

    // Filter down the list of all user items that are finished.
    done: function() {
      return this.where({done: true});
    },

    // Filter down the list to only user items that are still not finished.
    remaining: function() {
      return this.where({done: false});
    },

    // We keep the Users in sequential order, despite being saved by unordered
    // GUID in the database. This generates the next order number for new items.
    nextOrder: function() {
      if (!this.length) return 1;
      return this.last().get('order') + 1;
    },

    // Users are sorted by their original insertion order.
    comparator: 'order'

  });


// 検索View.
var SearchView  = Marionette.ItemView.extend({

	template: '#SearchViewTemplate',
	templateHelpers: function() { return {}; },

	ui: {
		addButton: "#add",
		editButton: "#edit",
		deleteButton: "#delete",
		viewButton: "#view",
		indexButton: "#index",
	},
	events: {
		"click @ui.allSelectButton" : "onClickAllSelectButton",
		"click @ui.importButton" : "onClickImportButton",
		"click @ui.addButton": "onClickAddButton",
		"click @ui.editButton": "onClickEditButton",
		"click @ui.deleteButton": "onClickDeleteButton",
		"click @ui.viewButton": "onClickViewButton",
		"click @ui.indexButton": "onClickIndexButton",
	},

	onClickAddButton: function() {

		var data = { User: {
			id: "5",
			username: "addTest",
			password: "add",
			role: "admin",
			created: "2014-10-22",
			modified: "2014-10-21"
			}
		};

		var url = "/users.json";

		$.ajax({
			data: data,
			type:"post",
			url: url,
		}).done(function(json, textStatus, jqXHR) {

			alert(json['message']);
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(jqXHR.responseText);
			alert(textStatus);
			obj_dump(errorThrown);
		});
	},
	onClickEditButton: function() {

		var data = { User: {
			username: "editTest",
			password: "edit",
			role: "author",
			created: "2014-10-20",
			modified: "2014-10-19"
			}
		};

		var url = "/users/2.json";

		$.ajax({
			data: data,
			type:"put",
			url: url,
		}).done(function(json, textStatus, jqXHR) {

			alert(json['message']);
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(jqXHR.responseText);
			alert(textStatus);
			obj_dump(errorThrown);
		});
	},
	onClickDeleteButton: function() {

		var url = "/users/5.json";

		$.ajax({
			type:"delete",
			url: url,
		}).done(function(json, textStatus, jqXHR) {

			alert(json['message']);
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(jqXHR.responseText);
			alert(textStatus);
			obj_dump(errorThrown);
		});
	},
	onClickViewButton: function() {

		var url = "/users/2.json";

		$.ajax({
			type:"get",
			url: url,
		}).done(function(json, textStatus, jqXHR) {

			_.each(json, function(value, key) {
				_.each(value, function(val, k) {
					obj_dump(val);
				});
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(jqXHR.responseText);
			alert(textStatus);
			obj_dump(errorThrown);
		});
	},
	onClickIndexButton: function() {

		var url = "/users.json";

		$.ajax({
			type:"get",
			url: url,
		}).done(function(json, textStatus, jqXHR) {

			_.each(json, function(value, key) {
				_.each(value, function(val, k) {
					_.each(val, function(v, k) {
						obj_dump(v);
					});
				});
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(jqXHR.responseText);
			alert(textStatus);
			obj_dump(errorThrown);
		});
	},

	initialize: function(options) {

		var headers = {

		};

		$.ajaxSetup({
			cache: false,
			async: true,
			dataType:"json",
//			headers: headers,
//			contentType : "",
//			mimeType: "",
			beforeSend: function(XMLHttpRequest){
				// アクセストークンをヘッダーにセットする必要がある.
				XMLHttpRequest.setRequestHeader('Authorization', 'Bearer dfc7bb558a0b91f781a8a5b58d2b94f2af5d2d12');
			},
		});
	},

	// View がレンダリングされて画面に表示された後に呼ばれるメソッド。
	onShow: function() {

		// Get rid of that pesky wrapping-div.
		// Assumes 1 child element present in template.
		this.$el = this.$el.children();
		// Unwrap the element to prevent infinitely 
		// nesting elements during re-render.
		this.$el.unwrap();
		this.setElement(this.$el);

		$('#index').on('click', this.onClickIndexButton);
		$('#view').on('click', this.onClickViewButton);
		$('#add').on('click', this.onClickAddButton);
		$('#edit').on('click', this.onClickEditButton);
		$('#delete').on('click', this.onClickDeleteButton);
	},
});

// アプリクラス.
var app = new Marionette.Application();

app.addRegions({
  filterRegion: "#filter",
});

app.addInitializer(function(options){

	// 検索部をrender.
	app.filterRegion.show(new SearchView({}));
});

app.on("start", function(options){

	if (Backbone.history){ Backbone.history.start(); }
});

$(function(){

	// backboneスタート.
	app.start();
});
