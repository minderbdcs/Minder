var CheckStrategyNonEdi = Backbone.Model.extend({

	messageBus : null,
	prodIdIndex: null,
	altIdIndex: null,
	productNotFoundDialogIsActive: false,
	forceCheckProducts: [],
	linesErrors: [],
	getRetailUnitUrl: '',
	acceptUrl: '',
	constantqty:'',

	defaults: {
		isSysAdmin: false
	},

	initialize: function(attributes, options) {
		this.messageBus = attributes.messageBus;
		this.bindMessageBusEvents();
		this.forceCheckProducts = attributes.forceCheckProducts;
		this.linesErrors = [];
		this.getRetailUnitUrl = attributes.getRetailUnitUrl;
		this.acceptUrl = options.acceptUrl;

		this.status = {
			isEdi: false,
			lastProdId: null,
			canBeChecked: attributes.canBeChecked,
			shouldCheckEachItem: true,
			shouldRecordSerialNumber: attributes.shouldRecordSerialNumber,
			recordingSerialNumbers: false,
			waitingForSuspendConfirmation: false,
			continueWithProdId: '',
			continueWithProdEan: '',
			allItemsChecked: false,
			totalSelectedItems: 0,
			pickedAmount: 0,
			uncheckedAmount: 0,
			uncheckedProducts: 0,
			checkDetailsLoaded: false,
			checkLineDetails: {},
			serialNumbers: []
		};

		if (attributes.selectedOrdersAmount > 0) {
			this.messageBus.notifyLoadLinesRequest();
		}
		this.listenTo(this, 'destroy', this.onDestroy);

		this._startProductChecking('', true, true);
	},

	bindMessageBusEvents: function() {
		this.messageBus.onLinesDespatchStatus(this.onLinesDespatchStatus, this);
		this.messageBus.onCheckNextProdIdRequest(this.onCheckNextProdIdRequest, this);
		this.messageBus.onDoProductCheckRequest(this.onDoProductCheckRequest, this);
		this.messageBus.onDoSerialNumberCheckRequest(this.onDoSerialNumberCheckRequest, this);
		this.messageBus.onConfirmSerialNumberSuspendRequest(this.onConfirmSerialNumberSuspendRequest, this);
		this.messageBus.onSerialNumberSuspendConfirmed(this.onSerialNumberSuspendConfirmed, this);
		this.messageBus.onCheckAllProdIdRequest(this.onCheckAllProdIdRequest, this);
		this.messageBus.onProductNotFoundDialogClose(this.onProductNotFoundDialogClose, this);
		this.messageBus.onResetCheckLineStatusesRequest(this.onResetCheckLineStatusesRequest, this);
		this.messageBus.onConnoteButtonClick(this.onConnoteButtonClick, this);
		this.messageBus.onScreenButtonAccept(this.onScreenButtonAccept, this);
		this.messageBus.onScreenButtonChecked(this.onScreenButtonChecked, this);
		this.messageBus.onScreenButtonCancel(this.onScreenButtonCancel, this);

		this.messageBus.onCheckNextProdEanRequest(this.onCheckNextProdEanRequest, this);
	},

	onScreenButtonCancel: function() {
		this.status.waitingForSuspendConfirmation = false;
		this.status.continueWithProdId = '';
		this.status.continueWithProdEan = '';
		this.messageBus.notifyCheckingStatusChanged(this.status);
	},

	onScreenButtonAccept: function() {

		if (this.status.waitingForSuspendConfirmation) {
			this.messageBus.notifyScreenButtonServed();
			this.messageBus.notifySerialNumberSuspendConfirmed(this.status.continueWithProdId, this.status.continueWithProdEan);
		}
	},

	onScreenButtonChecked: function() 
{
							if($('#barcode_value').val()!=''){
		 				
		 					$('#barcode_value').val('CHECKED');

			 				foundCheckDetails = this.getCheckDetails(this.status.lastProdId);

								if(foundCheckDetails.length>0){
											foundCheckDetails[0].CHECKED_QTY=foundCheckDetails[0].PICKED_QTY-1;	
											this._doProductCheck(foundCheckDetails);	
								}				
							showErrors(['All items checked.']);
						}	
	},


	onSerialNumberSuspendConfirmed: function(continueWithProdId, continueWithProdEan) {
		this.status.waitingForSuspendConfirmation = false;
		this.status.continueWithProdId = '';
		this.status.continueWithProdEan = '';

		if (continueWithProdId) {
			this._startProductChecking(continueWithProdId);
			this.messageBus.notifyCheckingStatusChanged(this.status);
		} else {
			this.checkRetailUnit(continueWithProdEan);
		}
	},

	onConfirmSerialNumberSuspendRequest: function(prodId, continueWithProdId, continueWithProdEan) {
		var uncheckedAmount = this.calculateUncheckedProductAmount(this.getCheckDetails(prodId));

		this.status.waitingForSuspendConfirmation = true;
		this.status.continueWithProdId = continueWithProdId;
		this.status.continueWithProdEan = continueWithProdEan;

		if (uncheckedAmount > 0) {
			this.messageBus.notifyCheckingStatusChanged(this.status);
		} else {
			this.messageBus.notifySerialNumberSuspendConfirmed(continueWithProdId, continueWithProdEan);
		}
	},

	onCheckNextProdEanRequest: function(prodEan) {
		this.checkRetailUnit(prodEan);
	},

	onProductNotFoundDialogClose: function() {
		this._startProductChecking('', true, true);
	},

	onLinesDespatchStatus: function(linesStatus) {
		this.prodIdIndex = null;
		this.altIdIndex = null;
		this.status.totalSelectedItems = linesStatus.totalSelectedItems;
		if (!this.status.checkDetailsLoaded) {
			this.status.checkLineDetails = linesStatus.checkLineDetails;
			this.status.serialNumbers = linesStatus.serialNumbers;
			this.status.checkDetailsLoaded = true;
		}

		this.forceCheck();

		this.status.uncheckedAmount = this.calculateUncheckedAmount(this.status.checkLineDetails);
		constantqty=this.status.uncheckedAmount;

		if (this.status.lastProdId) {
			this.status.uncheckedProducts = this.calculateUncheckedProductAmount(this.getCheckDetails(this.status.lastProdId));
		} else {
			this.status.uncheckedProducts = 0;
		}

		this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);

		this.messageBus.notifyCheckingStarted(this.status);
		this.messageBus.notifyCheckingStatusChanged(this.status);

		if (this.status.allItemsChecked) {
			this.messageBus.notifyCheckingComplete(this.status);
		} else {
			this._startProductChecking(this.status.lastProdId, true, true);
		}

		this.linesErrors = [].concat(linesStatus.errors || [], linesStatus.warnings || []);
		this.showConnote(true);
	},

	forceCheck: function() {
		this.forceCheckProducts.forEach($.proxy(this.forceCheckProdId, this));
		this.forceCheckProducts = [];
	},

	forceCheckProdId: function(prodId) {
		this.getCheckDetails(prodId).forEach(function(lineDetail){
			lineDetail.CHECKED_QTY = lineDetail.PICKED_QTY;
		});
	},

	onDoSerialNumberCheckRequest: function(prodId, serialNumber) {
		var foundCheckDetails,
			foundSerialNumber;

		this.status.lastProdId = prodId;
		foundCheckDetails = this.getCheckDetails(this.status.lastProdId);

		if (foundCheckDetails.length > 0) {
			foundSerialNumber = _.findWhere(this.status.serialNumbers, {PROD_ID: prodId, SERIAL_NUMBER: serialNumber});

			if (foundSerialNumber) {
				showErrors(['Serial number ' + serialNumber + ' already checked.']);
			} else {
				this.status.serialNumbers.push({
					PROD_ID: prodId,
					SERIAL_NUMBER: serialNumber,
					PICK_LABEL_NO: foundCheckDetails[0].PICK_LABEL_NO
				});
				foundCheckDetails[0].CHECKED_QTY++;

				this.status.uncheckedProducts = this.calculateUncheckedProductAmount(foundCheckDetails);
				this.status.uncheckedAmount--;
				this.messageBus.notifyCheckingStatusChanged(this.status);

				this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);
				if (this.status.allItemsChecked) {
					this.messageBus.notifyCheckingComplete(this.status);
				}
			}
		} else {

			showErrors(['All items checked.']);

			this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);
			if (this.status.allItemsChecked) {
				this.messageBus.notifyCheckingComplete(this.status);
			}
		}

	},

	_doProductCheck: function(foundCheckDetails,prodId=null) {
		

/***************************************************/

                        var urlpath = document.getElementById('urlcheckinqty').value;
			
		// ajax to select checkinqty 

			var test=$.ajax({
				data: 'prod_id='+prodId,
				type: 'POST',
				url: urlpath,
 				async:false

			});
			
			var checkinqty=test.responseText;
			foundCheckDetails[0].CHECKED_QTY=checkinqty;
			this.messageBus.notifyCheckingStatusChanged(this.status);
		if ((foundCheckDetails.length > 0)&&(checkinqty!=foundCheckDetails[0].PICKED_QTY)) {
			foundCheckDetails[0].CHECKED_QTY++;

			this.status.uncheckedProducts = this.calculateUncheckedProductAmount(foundCheckDetails,prodId);

                          
			var urlpath = document.getElementById('urlprodid').value;
			
		// ajax to pass prodId 

			$.ajax({
				data: 'prod_id='+prodId,
				type: 'POST',
				url: urlpath,
 				success: function(data){
					//alert(data);
    				},
				error: function(){
					alert("error");
				}

			});

			this.status.uncheckedAmount--;
			if($('#barcode_value').val()=="CHECKED"){

					this.status.uncheckedAmount=constantqty-foundCheckDetails[0].PICKED_QTY;
					constantqty=this.status.uncheckedAmount;
			}
			if(this.status.uncheckedProducts==0) {
				showMessage(['Scanning completed for product : '+prodId]);
			}

			this.messageBus.notifyCheckingStatusChanged(this.status);
		} else {
			showErrors(['All items checked.']);
		}

/***************************************************/
		this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);
		if (this.status.allItemsChecked) {

			this.messageBus.notifyCheckingComplete(this.status);
		}
	},

	onDoProductCheckRequest: function(prodId) {
		this.status.lastProdId = prodId;
		this._doProductCheck(this.getCheckDetails(this.status.lastProdId),prodId);
	},

	_clearProductCheckingStrategy: function() {
		if (this.checkProductStrategy) {
			this.checkProductStrategy.remove();
			this.checkProductStrategy = null;
		}
	},

	_getNextProductCheckingStrategy: function(prodId, productDetails, silent, suppressNotFound) {
		if (prodId && (productDetails.length > 0)) {
			if (this.status.shouldRecordSerialNumber) {
				if (productDetails[0].PROD_TYPE.toUpperCase() == 'SN') {
					return new ProductCheckStrategySerialNumber({messageBus: this.messageBus, prodId: prodId, silent: silent});
				} else {
					return new ProductCheckStrategyGeneral({messageBus: this.messageBus, prodId: prodId, silent: silent});
				}
			} else {
				return new ProductCheckStrategyGeneral({messageBus: this.messageBus, prodId: prodId, silent: silent});
			}
		} else {
			if (suppressNotFound) {
				return new ProductCheckStrategyGeneral({messageBus: this.messageBus, prodId: prodId, silent: silent});
			} else {
				      //execute procedure 

	         var prodpath = document.getElementById('getprodid').value;
		
		 var check_pid = $.ajax({
				data: 'prod_id='+prodId,
				type: 'POST',
				url: prodpath,
 				async:false
			               });

				var prod_status = check_pid.responseText;
				
				if(prod_status=="1")
				{
					
					return new ProductCheckStrategySerialNumber({messageBus: this.messageBus, prodId: prodId, silent: silent});
				}
				else
				{
				
				return new ProductCheckStrategyNotFound({messageBus: this.messageBus, prodId: prodId});
				}
			}
		}
	},

	_startProductChecking: function(prodId, silent, suppressNotFound) {
		var productDetails;

		this._clearProductCheckingStrategy();

		if (!this.status.canBeChecked) {
			this.checkProductStrategy = new ProductCheckStrategyNotPickedYet({messageBus: this.messageBus, silent: silent});
			return;
		}

		this.status.pickedAmount = 0;
		this.status.recordingSerialNumbers = false;
		productDetails = this.getProduct(prodId);
		this.checkProductStrategy = this._getNextProductCheckingStrategy(prodId, productDetails, silent, suppressNotFound);

		if (prodId && (productDetails.length > 0)) {
			this.status.pickedAmount = this.calculatePickedProductAmount(productDetails);
		}

		if (this.checkProductStrategy.immidiateCheck) {
			if (!silent) {
				this.messageBus.notifyCheckProdIdRequest(prodId);
			}
		}

		if (this.checkProductStrategy.recordingSerialNumber) {
			this.status.recordingSerialNumbers = true;
			this.status.lastProdId = prodId;
			this.status.uncheckedProducts = this.calculateUncheckedProductAmount(this.getCheckDetails(prodId));
			this.messageBus.notifyCheckingStatusChanged(this.status);
		}
	},

	onCheckNextProdIdRequest: function(prodId) {
		this._startProductChecking(prodId);
	},

	onCheckAllProdIdRequest: function(prodId) {
		var foundCheckDetails;

		foundCheckDetails = this.getCheckDetails(prodId);

		if (foundCheckDetails.length > 0) {

			foundCheckDetails.forEach(function(lineDetail) {
				lineDetail.CHECKED_QTY = lineDetail.PICKED_QTY;
			});

			this.status.uncheckedProducts = 0;
			this.status.uncheckedAmount = this.calculateUncheckedAmount(this.status.checkLineDetails);

			this.messageBus.notifyCheckingStatusChanged(this.status);
		} else {

			showErrors(['All items checked.']);
		}

		this.status.allItemsChecked = (this.status.totalSelectedItems > 0) && (this.status.uncheckedAmount < 1);
		if (this.status.allItemsChecked) {
			this.messageBus.notifyCheckingComplete(this.status);
		}
	},

	onResetCheckLineStatusesRequest: function() {
		$.each(this.status.checkLineDetails, function(index, lineDetail){
			lineDetail.CHECKED_QTY = 0;
		});

		this.status.lastProdId = null;
		this.status.uncheckedProducts = 0;
		this.status.allItemsChecked = false;
		this.status.uncheckedAmount = this.calculateUncheckedAmount(this.status.checkLineDetails);
		this.status.serialNumbers = [];
		this.status.waitingForSuspendConfirmation = false;
		this.status.continueWithProdId = '';
		this.status.continueWithProdEan = '';
		this.status.recordingSerialNumbers = false;
		this.status.pickedAmount = 0;
		this.messageBus.notifyCheckingStatusChanged(this.status);
	},

	checkRetailUnit: function(code) {
		$.get(this.getRetailUnitUrl, {'code': code}, $.proxy(this.checkRetailUnitCallback, this), 'json');
	},

	checkRetailUnitCallback: function(response) {
		var uncheckedProdIdAmount,
			prodIssueQty,
			productDetails,
			prodId;

		showResponseMessages(response);
		if (response.retailUnit) {
			this._clearProductCheckingStrategy();

			prodId = response.retailUnit.PROD_ID;
			this.status.pickedAmount = 0;
			this.status.recordingSerialNumbers = false;
			productDetails = this.getProduct(prodId);
			this.checkProductStrategy = this._getNextProductCheckingStrategy(prodId, productDetails, true, true);

			if (prodId && (productDetails.length > 0)) {
				this.status.pickedAmount = this.calculatePickedProductAmount(productDetails);
			}

			if (this.checkProductStrategy.immidiateCheck) {
				uncheckedProdIdAmount = this.calculateUncheckedProductAmount(this.getCheckDetails(prodId));

				if (uncheckedProdIdAmount > 0) {
					if (uncheckedProdIdAmount < response.retailUnit.PROD_ISSUE_QTY) {
						showErrors(['Retail Unit issue qty more then unchecked products left.']);
					} else {
						this.status.lastProdId = prodId;
						prodIssueQty = response.retailUnit.PROD_ISSUE_QTY || 0;

						while (prodIssueQty > 0) {
							this._doProductCheck(this.getCheckDetails(this.status.lastProdId));
							prodIssueQty--;
						}
					}
				} else {


					showErrors(['All items checked.']);
				}
			}

			if (this.checkProductStrategy.recordingSerialNumber) {
				this.status.recordingSerialNumbers = true;
				this.status.lastProdId = prodId;
				this.status.uncheckedProducts = this.calculateUncheckedProductAmount(this.getCheckDetails(prodId));
				this.messageBus.notifyCheckingStatusChanged(this.status);
			}


		}
	},

	calculatePickedProductAmount: function(productCheckDetails) {
		return productCheckDetails.reduce(function(previousValue, lineDetail){
			var picked = 0;

			if (lineDetail.SELECTED) {
				picked      = parseInt(lineDetail.PICKED_QTY) || 0;
			}

			return previousValue + Math.abs(picked);
		}, 0);
	},

	calculateUncheckedProductAmount: function(productCheckDetails,prodId=null) {
		return productCheckDetails.reduce(function(previousValue, lineDetail){
			var picked = 0,
				checked = 0;

			if (lineDetail.SELECTED) {
				picked      = parseInt(lineDetail.PICKED_QTY) || 0;
				checked     = parseInt(lineDetail.CHECKED_QTY) || 0;
			}

			
			return previousValue + Math.abs(picked - checked);

		}, 0);
	},

	calculateUncheckedAmount: function(checkLineDetails) {
		var
			uncheckedAmount = 0;

		$.each(checkLineDetails, function(key, lineDetail) {
			var
				picked, checked;
			if (lineDetail.SELECTED) {
				picked      = parseInt(lineDetail.PICKED_QTY);
				picked      = isNaN(picked) ? 0 : picked;
				checked     = parseInt(lineDetail.CHECKED_QTY);
				checked     = isNaN(checked) ? 0 : checked;

				uncheckedAmount += Math.abs(picked - checked);
			}
		});

		return uncheckedAmount;
	},

	buildIndex: function(fieldName) {
		var result = {
			selected: {},
			all: {}
		};

		$.each(this.status.checkLineDetails, function(index, lineDetail) {
			if (lineDetail.SELECTED) {
				result.selected[lineDetail[fieldName]] = result.selected[lineDetail[fieldName]] || [];
				result.selected[lineDetail[fieldName]].push(lineDetail);
			}
			result.all[lineDetail[fieldName]] = result.all[lineDetail[fieldName]] || [];
			result.all[lineDetail[fieldName]].push(lineDetail);
		});

		return result;
	},

	getProduct: function(prodId) {
		if (!this.prodIdIndex) {
			this.prodIdIndex = this.buildIndex('PROD_ID');
		}

		if (!this.altIdIndex) {
			this.altIdIndex = this.buildIndex('ALTERNATE_ID');
		}

		return this.prodIdIndex.selected[prodId] || this.altIdIndex.selected[prodId] || [];
	},

	hasProdId: function(prodId) {
		return this.getProduct(prodId).length > 0;
	},

	getCheckDetails: function(prodId) {
		var result = this.getCheckLineDetailsByProdId(prodId);
		return result.length > 0 ? result : this.getCheckDetailsByAlternateId(prodId);
	},

	getCheckLineDetailsByProdId: function(prodId) {
		if (!this.prodIdIndex) {
			this.prodIdIndex = this.buildIndex('PROD_ID');
		}


		return this.prodIdIndex.selected[prodId] ? this.prodIdIndex.selected[prodId].filter(this.isUnchecked) : []
	},

	getCheckDetailsByAlternateId: function(prodId) {
		if (!this.altIdIndex) {
			this.altIdIndex = this.buildIndex('ALTERNATE_ID');
		}

		return this.altIdIndex.selected[prodId] ? this.altIdIndex.selected[prodId].filter(this.isUnchecked) : []
	},

	isUnchecked: function(lineDetail) {



		return parseInt(lineDetail.PICKED_QTY) > parseInt(lineDetail.CHECKED_QTY);
	},

	onConnoteButtonClick: function() {
		this.showConnote(false);
	},

	showConnote: function(silent) {
		if (this.linesErrors.length > 0) {
			if (!silent) {
				showErrors(this.linesErrors);
			}

			if (!this.attributes.isSysAdmin) {
				return;
			}
		}

		this.messageBus.notifyShowConnote(this.acceptUrl);
	},

	onDestroy: function() {
		if (this.checkProductStrategy) {
			this.checkProductStrategy.remove();
			this.checkProductStrategy = null;
		}
		this.stopListening();
	}

});
