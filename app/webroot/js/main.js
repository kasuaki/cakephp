
function obj_dump(obj) {
	var txt = '';
	for (var one in obj){
		txt += one + "=" + obj[one] + "\n";
	}
	alert(txt);
}

var User = Backbone.Model.extend({

	url: '/users',

	defaults: {
		"id" : null,
		"username" : null,
		"password" : null,
		"role" : null,
		"created" : null,
		"modified" : null,
	},

	// 独自Sync.
	// Map from CRUD to HTTP for our default `Backbone.sync` implementation.
	// var methodMap = {
	//   'create': 'POST',
	//   'update': 'PUT',
	//   'patch':  'PATCH',
	//   'delete': 'DELETE',
	//   'read':   'GET'
	// };
	sync: function(method, model, options) {

		switch(method) {
			case 'read':	// GET.
			case 'update':	// PUT.
			case 'delete':	// DELETE.
				options.url = model.url + "/" + model.get('id') + ".json";
				break;
			case 'create':	// POST.
			default:
				options.url = model.url + ".json";
				break;
		}

		return Backbone.sync(method, model, options);
	},
});

var UserList = Backbone.Collection.extend({

  // Reference to this collection's model.
  model: User,

  // Save all of the User items under the `"users-backbone"` namespace.
//    localStorage: new Backbone.LocalStorage("users-backbone"),

  url: '/users',

  parse: function(response, xhr) {

	var users = response.users != undefined ? response.users : response;

    return users;
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
  comparator: 'id'

});

var TableItemView = Backbone.Marionette.ItemView.extend({

	tagName: "tr",
	model: User,
	template: "#ItemViewTemplate",
	templateHelpers: function() {
		return {};
	},

	// ViewとModelのBinding.
	bindings: {
		'td#id': 'id',
		'td#username': 'username',
		'td#password': 'password',
		'td#role': 'role',
		'td#created': 'created',
		'td#modified': 'modified',
	},

	initialize: function(options) {
	},

	// View がレンダリングされた後に呼ばれるメソッド。
	onRender: function() {
	},

	// View がレンダリングされて画面に表示された後に呼ばれるメソッド。
	onShow: function() {
		this.stickit();
	},
});

// 検索View.
var SearchView  = Marionette.CompositeView.extend({

	itemView: TableItemView,
//	itemViewOptions : function () {	},
    itemViewContainer: '#body',

	template: '#SearchViewTemplate',
	templateHelpers: function() {
		return {};
	},

	ui: {
		addButton: "#add",
		addAjaxButton: "#addAjax",
		editButton: "#edit",
		editAjaxButton: "#editAjax",
		deleteButton: "#delete",
		deleteAjaxButton: "#deleteAjax",
		viewButton: "#view",
		viewAjaxButton: "#viewAjax",
		indexButton: "#index",
		indexAjaxButton: "#indexAjax",
		eventName: "#eventName"
	},
	events: {
		"click @ui.allSelectButton" : "onClickAllSelectButton",
		"click @ui.importButton" : "onClickImportButton",
		"click @ui.addButton": "onClickAddButton",
		"click @ui.addAjaxButton": "onClickAddAjaxButton",
		"click @ui.editButton": "onClickEditButton",
		"click @ui.editAjaxButton": "onClickEditAjaxButton",
		"click @ui.deleteButton": "onClickDeleteButton",
		"click @ui.deleteAjaxButton": "onClickDeleteAjaxButton",
		"click @ui.viewButton": "onClickViewButton",
		"click @ui.viewAjaxButton": "onClickViewAjaxButton",
		"click @ui.indexButton": "onClickIndexButton",
		"click @ui.indexAjaxButton": "onClickIndexAjaxButton",
	},

	initialize: function(options) {

		// イベント監視.
		this.collection.on("all", _.bind(function(eventName, a, b, c) {
			var self = this;
			var old = self.ui.eventName.text();
			self.ui.eventName.text(old + " " + eventName);

			switch(eventName) {
				case "error":
					var model = a;
					var xhr = b;
					var options = c;

					obj_dump(xhr);
					break;
			}
		}, this));

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
				XMLHttpRequest.setRequestHeader('Authorization', 'Bearer 3be96407d96e5f949b946294f76e04875da07242');
			},
		});
	},

	onClickAddButton: function() {

		var self = this;
		var data = { 
			username: "addTest",
			password: "add",
			role: "admin"
		};

		var model = new self.collection.model();

		model.on('sync', _.bind(function(model, resp, options) {

			model.off('sync');

			this.collection.add(model);
		}, self));
		model.save(data);
	},

	onClickAddAjaxButton: function() {

		var data = { User: {
			username: "addTest",
			password: "add",
			role: "admin"
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

	onClickEditAjaxButton: function() {

		var self = this;
		var model = self.collection.last();

		var data = { User: {
			username: "editTest",
			password: "edit",
			role: "author"
			}
		};

		var url = "/users/" + model.get('id') + ".json";

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

	onClickEditButton: function() {

		var self = this;
		var model = self.collection.last();

		var data = {
			username: "editTest",
			password: "edit",
			role: "author"
		};

		model.set(data);
		model.save();
	},

	onClickDeleteButton: function() {

		var self = this;
		var model = self.collection.last();

		model.on('destroy', _.bind(function(model, collection, options) {

			this.off('destroy');

			collection.remove(model);
		}, self));

		model.destroy();
	},

	onClickDeleteAjaxButton: function() {

		var self = this;
		var model = self.collection.last();

		var url = "/users/" + model.get('id') + ".json";

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
		var self = this;
		var model = self.collection.last();

		model.fetch();
	},

	onClickViewAjaxButton: function() {

		var url = "/users/6.json";

		$.ajax({
			type:"get",
			url: url,
		}).done(function(json, textStatus, jqXHR) {

			obj_dump(json);
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(jqXHR.responseText);
			alert(textStatus);
			obj_dump(errorThrown);
		});
	},

	onClickIndexButton: function() {
		var self = this;
		self.collection.fetch();
	},

	onClickIndexAjaxButton: function() {

		var url = "/users.json";

		$.ajax({
			type:"get",
			url: url,
		}).done(function(json, textStatus, jqXHR) {

			_.each(json, function(value, key) {
				obj_dump(value);
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(jqXHR.responseText);
			alert(textStatus);
			obj_dump(errorThrown);
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

		this.collection.fetch();
	},
});

// アプリクラス.
var app = new Marionette.Application();

app.addRegions({
  filterRegion: "#filter",
});

app.addInitializer(function(options){

	// 検索部をrender.
	app.filterRegion.show(new SearchView({collection: new UserList({})}));
});

app.on("start", function(options){

	if (Backbone.history){ Backbone.history.start(); }
});

$(function(){

	// backboneスタート.
	app.start();
});
