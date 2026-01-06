(function($) {
 
var
 replaceMethodContent = function(object) {
  for(var i in object) {
   if (typeof object[i] === 'function' && i !== '__self') {
    (function(f) {
     object[i] = function() {
      return f.apply(object, arguments);
     }
    })(object[i]);
   }
  }
 }

var
 hasIntrospection = (function(){_}).toString().indexOf('_') > -1,
 emptyBase = function() {}
 ;

$.inherit = function() {
 var
  hasBase = $.isFunction(arguments[0]),
  base = hasBase? arguments[0] : emptyBase,
  props = arguments[hasBase? 1 : 0] || {},
  staticProps = arguments[hasBase? 2 : 1],
  result = props.__constructor || base.prototype.__constructor?
   function() {
    replaceMethodContent(this);
    this.__constructor.apply(this, arguments);
   } : function() {
    replaceMethodContent(this);
   },
  inheritance = function() {}
  ;

 $.extend(result, base, staticProps);

 replaceMethodContent(result);

 inheritance.prototype = base.prototype;
 result.prototype = new inheritance();
 result.prototype.__self = result.prototype.constructor = result;

 var propList = [];
 $.each(props, function(i) {
  if(props.hasOwnProperty(i)) {
   propList.push(i);
  }
 });
 // fucking ie hasn't toString, valueOf in for
 $.each(['toString', 'valueOf'], function() {
  if(props.hasOwnProperty(this) && $.inArray(this, propList) == -1) {
   propList.push(this);
  }
 });

 $.each(propList, function() {
  if(hasBase
   && $.isFunction(base.prototype[this]) && $.isFunction(props[this])
   && (!hasIntrospection || props[this].toString().indexOf('.__base') > -1)) {

   (function(methodName) {
    var
     baseMethod = base.prototype[methodName],
     overrideMethod = props[methodName]
     ;
    result.prototype[methodName] = function() {
     var baseSaved = this.__base;
     this.__base = baseMethod;
     var result = overrideMethod.apply(this, arguments);
     this.__base = baseSaved;
     return result;
    };
   })(this);

  }
  else {
   result.prototype[this] = props[this];
  }
 });
 

 return result;

};

})(jQuery);