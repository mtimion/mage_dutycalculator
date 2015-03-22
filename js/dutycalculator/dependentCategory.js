var allDependentCategories=new Array
function dependentCategory(name,reference){
	this.name=name
	this.reference=reference
	this.childs=new Array()
	this.level=1
	this.id=allDependentCategories.length
	allDependentCategories[this.id]=this
}
dependentCategory.prototype={
	addChild:function(category){
		this.childs.push(category)
		category.level=this.level+1
	},
	createOptions:function(selectbox, prefilledValue){
		if(selectbox){
			if(!selectbox.childOptions){
				selectbox.childOptions=new Array()
			}
			if (prefilledValue && prefilledValue.length > 0)
			{
				var newOption = document.createElement('option')
				selectbox.childOptions.push(newOption)
				newOption.appendChild(document.createTextNode('......................................................'))
				newOption.value = ' '
				newOption.title = '......................................................';
				newOption.disabled = true;
				selectbox.appendChild(newOption)

				for (var x = 0; x < this.childs.length; x++) {
					var category = this.childs[x]
					for (var p = 0; p < prefilledValue.length; p++)
					{
						if (prefilledValue[p].id == category.reference)
						{
							var newOption = document.createElement('option')
							selectbox.childOptions.push(newOption)
							newOption.appendChild(document.createTextNode(category.name))
							newOption.value = category.reference
							newOption.title = category.name
							if (prefilledValue[p].items)
							{
								newOption.prefilledValue = prefilledValue[p].items
							}
							selectbox.appendChild(newOption)
						}
					}

				}

				var newOption = document.createElement('option')
				selectbox.childOptions.push(newOption)
				newOption.appendChild(document.createTextNode('......................................................'))
				newOption.value = '  '
				newOption.title = '......................................................';
				newOption.disabled = true;
				selectbox.appendChild(newOption)

				for (var x = 0; x < this.childs.length; x++) {
					var category = this.childs[x]
					canAdd = true;
					for (var p = 0; p < prefilledValue.length; p++)
					{
						if (prefilledValue[p].id == category.reference)
						{
							canAdd = false;
						}
					}
					if (canAdd)
					{
						var newOption = document.createElement('option')
						selectbox.childOptions.push(newOption)
						newOption.appendChild(document.createTextNode(category.name))
						newOption.value = category.reference
						newOption.title = category.name
						selectbox.appendChild(newOption)
					}
				}
			}
			else
			{
				for (var x = 0; x < this.childs.length; x++) {
					var category = this.childs[x]
					var newOption = document.createElement('option')
					selectbox.childOptions.push(newOption)
					newOption.appendChild(document.createTextNode(category.name))
					newOption.value = category.reference
					newOption.title = category.name
					selectbox.appendChild(newOption)
				}
			}
			if (prefilledValue && prefilledValue.length == 1) {
				selectbox.value = prefilledValue[0].id
			}
		}
	},
	expand : function(selectbox, prefilledValue){
		if(selectbox.expanded){
			return false
		}
		if(this.childs.length){
			selectbox.expanded=true
			this.createOptions(selectbox, prefilledValue)
			selectbox.readonly=false
		}
	},
	collapse : function(selectbox){
		if(selectbox.expanded){
			for(var x=0;x<selectbox.childOptions.length;x++){
				selectbox.childOptions[x].parentNode.removeChild(selectbox.childOptions[x])
			}
			selectbox.childOptions=new Array()
			selectbox.expanded=false
			selectbox.readonly=true
			for(var x=0;x<this.childs.length;x++){
				this.childs[x].collapse(selectbox)
			}
		}
	},
	getChildByReference : function(reference){
		for(var x=0;x<this.childs.length;x++){
			if(this.childs[x].reference==reference){
				return this.childs[x]
			}
		}
	}
}

function getDependentCategoryItemByReferenceAndLevel(reference,level){
	for(var x=0;x<allDependentCategories.length;x++){
		if(allDependentCategories[x].reference==reference && allDependentCategories[x].level==level){
			return allDependentCategories[x]
		}
	}
}
function getOrCreateDependentCategoryItemByReferenceAndLevel(name,reference,level){
	for(var x=0;x<allDependentCategories.length;x++){
		if(allDependentCategories[x].reference==reference && allDependentCategories[x].level==level){
			return allDependentCategories[x]
		}
	}
	return new dependentCategory(name,reference)
}

function changeCategory(obj, prefilledValue)
{
	if (obj.value)
	{
		if (secondCategory)
		{
			secondCategory.collapse($('dc_product_sub_category'))
		}
		if (thirdCategory)
		{
			thirdCategory.collapse($('dc_product_item'));
			thirdCategory = null
		}
		secondCategory = firstCategory.getChildByReference(obj.value);
		if (prefilledValue)
		{
			secondCategory.expand($('dc_product_sub_category'), prefilledValue)
		}
		else if (obj.options[obj.selectedIndex].prefilledValue)
		{
			secondCategory.expand($('dc_product_sub_category'), obj.options[obj.selectedIndex].prefilledValue)
			if (obj.options[obj.selectedIndex].prefilledValue.length == 1)
			{
				changeSubCategory($('dc_product_sub_category'), obj.options[obj.selectedIndex].prefilledValue[0].items)
			}
		}
		else
		{
			secondCategory.expand($('dc_product_sub_category'))
		}
	}
	else
	{
		if (secondCategory)
		{
			secondCategory.collapse($('dc_product_sub_category'));
			secondCategory = null
			if (thirdCategory)
			{
				thirdCategory.collapse($('dc_product_item'));
				thirdCategory = null
			}
		}
	}
}

function changeSubCategory(obj, prefilledValue)
{
	if (obj.value)
	{
		if (thirdCategory)
		{
			thirdCategory.collapse($('dc_product_item'))
		}
		thirdCategory = secondCategory.getChildByReference(obj.value);
		if (prefilledValue)
		{
			thirdCategory.expand($('dc_product_item'), prefilledValue)
		}
		else if (obj.options[obj.selectedIndex].prefilledValue)
		{
			thirdCategory.expand($('dc_product_item'), obj.options[obj.selectedIndex].prefilledValue)
		}
		else
		{
			thirdCategory.expand($('dc_product_item'))
		}
	}
	else
	{
		if (thirdCategory)
		{
			thirdCategory.collapse($('dc_product_item'));
			thirdCategory = null
		}
	}
}