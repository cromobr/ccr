<script language="JavaScript" type="text/javascript">
<!-- 	***************FORMULAR VALIDATION********************

	var checkNoForm = false;
	window.onload   = initValidation;

	function initValidation() {
		var objForm = document.forms["commentform"]; if (!objForm) return true;
		
		// TEST COMMENT MESSAGE
		objForm.body.required = 1;
		objForm.body.regexp   = /([-\w<?php echo $this->lang['specialchars'];?>]{3,12}[\s\?\.,;:!]+){1,}/;
		objForm.body.realname = "<?php echo $this->lang['comment'];?>";
		objForm.body.oldClassName = "comment-textfield";

		// TEST COMMENT NAME
		objForm.name.required = 1;
		objForm.name.regexp   = /^[\s\w\.,-;<?php echo $this->lang['specialchars'];?>]{2,90}$/;
		objForm.name.realname = "<?php echo $this->lang['name'];?>";
		objForm.name.oldClassName = "comment-textfield";

		// TEST COMMENT E-MAIL
		objForm.email.required = 1;
		objForm.email.regexp   = "JSVAL_RX_EMAIL";
		objForm.email.realname = "<?php echo $this->lang['email'];?>";
		objForm.email.oldClassName = "comment-textfield";

		// TEST COMMENT CAPTCHA IF IT USED
		if (objForm.captcha) {
			objForm.captcha.required = 1;
			objForm.captcha.regexp   = /^\s*[2-9a-zA-Z]{6}\s*$/;
			objForm.captcha.realname = "<?php echo $this->lang['captcha'];?>";
			objForm.captcha.oldClassName = "comment-textfield";
		}
	}

	function jsVal_Language() {
		this.err_form  = "<?php echo $this->lang['commentmissing'];?>\n\n";
		this.err_enter = "<?php echo $this->lang['commentmissing'];?> \"%FIELDNAME%\"";
	}

/////////////////////////////////////////////////////////////////////////
// ************************* BEGIN JSVAL 1.3.4 ************************* //
/////////////////////////////////////////////////////////////////////////

// CALL BY ONSUBMIT EVENTHANDLER
function validateCompleteForm(objForm, strErrorClass) {

	return (checkNoForm == false)?
			 _validateInternal(objForm, strErrorClass, 0)
			 :
			 true;	// switch off form check
}

/***************************************************************
** Internal functions
*****************************************************************/
function _validateInternal(form,strErrorClass,nErrorThrowType){
var strErrorMessage="";var objFirstError=null;if(nErrorThrowType==0){
strErrorMessage=(form.err)? form.err:_getLanguageText("err_form");}
var fields=_GenerateFormFields(form);for(var i=0;i<fields.length;++i){
var field=fields[i];if(!field.IsValid(fields)){field.SetClass(strErrorClass);
if(nErrorThrowType==1){_throwError(field);return false;}else{
if(objFirstError==null){objFirstError=field;}
strErrorMessage=_handleError(field,strErrorMessage);
bError=true;}}else{field.ResetClass();}}
if(objFirstError != null){alert(strErrorMessage);objFirstError.element.focus();
return false;}
return true;}
function _getLanguageText(id){objTexts=new jsVal_Language();switch(id){
case "err_form":return (objTexts && objTexts.err_form)? objTexts.err_form:"";
case "err_enter":return (objTexts && objTexts.err_enter)? objTexts.err_enter:"";
case "err_select":return (objTexts && objTexts.err_select)? objTexts.err_select:"";}}
function _GenerateFormFields(form){var arr=new Array();for(var i=0;i<form.length;++i){
var element=form.elements[i];var index=_getElementIndex(arr,element);
if(index==-1){arr[arr.length]=new Field(element,form);}}return arr;}
function _getElementIndex(arr,element){if(element.name){
var elementName=element.name.toLowerCase();for(var i=0;i<arr.length;++i){
if(arr[i].element.name){
if(arr[i].element.name.toLowerCase()==elementName) return i;}}}return -1;}
function Field(element,form){this.type=element.type;this.element=element;
this.exclude=element.exclude;this.err=element.err;
this.required=_parseBoolean(element.required);this.realname=element.realname;
this.elements=new Array();switch (this.type){
case "textarea":
case "password":
case "text":
case "file":this.value=element.value;this.minLength=element.minlength;
this.maxLength=element.maxlength;this.regexp=this._getRegEx(element);
this.minValue=element.minvalue;this.maxValue=element.maxvalue;
this.equals=element.equals;this.callback=element.callback;break;}}
Field.prototype.IsValid=function(arrFields){switch (this.type){
case "textarea":
case "password":
case "text":
case "file":
return this._ValidateText(arrFields);default:return true;}}
Field.prototype.SetClass=function(newClassName){
if((newClassName)&&(newClassName!="")){if(this.element.className!=newClassName){
this.element.oldClassName=this.element.className;this.element.className=newClassName;}}}
Field.prototype.ResetClass=function(){
if((this.type!="button")&&(this.type!="submit")&&(this.type!="reset")){
if((this.elements)&&(this.elements.length>0)){
for(var i=0;i<this.elements.length;++i){if(this.elements[i].oldClassName){
this.elements[i].className=this.elements[i].oldClassName;
}else{this.element.className="";}}}else{if (this.element.oldClassName)
{this.element.className=this.element.oldClassName;}
else{this.element.className="";}}}}
Field.prototype._getRegEx=function(element){regex=element.regexp;
if(regex==null) return null;retype=typeof(regex);if(retype.toUpperCase()=="STRING")
if(retype.toUpperCase()!="FUNCTION")
if(regex != "JSVAL_RX_EMAIL"){nBegin=0;nEnd=0;if(regex.charAt(0)=="/")nBegin=1;
if(regex.charAt(regex.length-1)=="/")nEnd=0;
return new RegExp(regex.slice(nBegin,nEnd));}return regex;}
Field.prototype._ValidateText=function(arrFields){if((this.required)&&(this.callback)){
nCurId=(this.element.id)? this.element.id:"";
nCurName=(this.element.name)? this.element.name:"";
eval("bResult="+this.callback+"('"+nCurId+"','"+nCurName+"','"+this.value+"');");return bResult;}
else {if(this.required && !this.value){return false;}
if(this.regexp){if(!_checkRegExp(this.regexp,this.value)){
if(!this.required && this.value){return false;}
if(this.required){return false;}}else{return true;}}}return true;}
function _handleError(field,strErrorMessage){var obj=field.element;
strNewMessage=strErrorMessage+((field.realname)? field.realname:((obj.id)? obj.id:obj.name))+"\n";
return strNewMessage;}
function _throwError(field){var obj=field.element;switch (field.type){
case "text":
case "password":
case "textarea":
case "file":alert(_getError(field,"err_enter"));try{obj.focus();}
catch(ignore){}
break;}}
function _getError(field,str){var obj=field.element;
strErrorTemp=(field.err)? field.err:_getLanguageText(str);
idx=strErrorTemp.indexOf("\\n");while(idx > -1){
strErrorTemp=strErrorTemp.replace("\\n","\n");idx=strErrorTemp.indexOf("\\n");}
return strErrorTemp.replace("%FIELDNAME%",(field.realname)? field.realname:((obj.id)? obj.id:obj.name));}
function _parseBoolean(value){return !(!value || value==0 || value=="0" || value=="false");}
function _checkRegExp(regx,value){switch(regx){case "JSVAL_RX_EMAIL":
<?php $cClass='[_a-zA-Z'.$this->lang['specialchars'].'0-9-]';?>
return (/^<?php echo $cClass;?>+(\.<?php echo $cClass;?>+)*@<?php echo $cClass;?>+(\.<?php echo $cClass;?>+)*\.\w{2,5}$/).test(value);
default:return regx.test(value);}}
-->
</script>