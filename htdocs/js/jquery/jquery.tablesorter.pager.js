(function($) {
	$.extend({
		tablesorterPager: new function() {
			
			function updatePageDisplay(c) {
				//var s = $(c.cssPageDisplay,c.container).val((c.page+1) + c.seperator + c.totalPages);
				var s = $(c.cssPageDisplay,c.container).val('Page ' + (c.page+1) + ' of ' + c.totalPages);	
			}
			
			function setPageSize(table,size) {
				var c = table.config;
				c.size = size;
				c.totalPages = Math.ceil(c.totalRows / c.size);
				c.pagerPositionSet = false;
				moveToPage(table);
				fixPosition(table);
			}
			
			function fixPosition(table) {
				var c = table.config;
				if(!c.pagerPositionSet && c.positionFixed) {
					var c = table.config, o = $(table);
					if(o.offset) {
						c.container.css({
							top: o.offset().top + o.height() + 'px',
							position: 'absolute'
						});
					}
					c.pagerPositionSet = true;
				}
			}
			
			function moveToThisPage(table, pageNo){
				var c = table.config;
				c.page = parseInt(pageNo);
				moveToPage(table);				
			}
			
			function moveToFirstPage(table) {
				var c = table.config;
				c.page = 0;
				moveToPage(table);
			}
			
			function moveToLastPage(table) {
				var c = table.config;
				c.page = (c.totalPages-1);
				moveToPage(table);
			}
			
			function moveToNextPage(table) {
				var c = table.config;
				c.page++;
				if(c.page >= (c.totalPages-1)) {
					c.page = (c.totalPages-1);
				}
				moveToPage(table);
			}
			
			function moveToPrevPage(table) {
				var c = table.config;
				c.page--;
				if(c.page <= 0) {
					c.page = 0;
				}
				moveToPage(table);
			}
						
			
			function moveToPage(table) {
				var c = table.config;
				$.cookie(cookieSearchPage, c.page, { path: '/'}); 
				if(c.page < 0 || c.page > (c.totalPages-1)) {
					c.page = 0;
				}
				
				renderTable(table,c.rowsCopy);
			}
			
			function renderTable(table,rows) {
				
				var c = table.config;
				var l = rows.length;
				var s = (c.page * c.size);
				var e = (s + c.size);
				if(e > rows.length ) {
					e = rows.length;
				}
				
				
				var tableBody = $(table.tBodies[0]);
				
				// clear the table body
				
				$.tablesorter.clearTableBody(table);
				
				for(var i = s; i < e; i++) {
					
					//tableBody.append(rows[i]);
					
					var o = rows[i];
					var l = o.length;
					for(var j=0; j < l; j++) {
						
						tableBody[0].appendChild(o[j]);

					}
				}
				
				fixPosition(table,tableBody);
				
				$(table).trigger("applyWidgets");
				
				if( c.page >= c.totalPages ) {
        			moveToLastPage(table);
				}
				
				updatePageDisplay(c);
			}
			
			this.appender = function(table,rows) {
				
				var c = table.config;
				
				c.rowsCopy = rows;
				c.totalRows = rows.length;
				c.totalPages = Math.ceil(c.totalRows / c.size);
				
				renderTable(table,rows);
			};

                    
            //create date for cookie            
            var date = new Date();
            date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));			
         
            //set cookie name   
			cookiePageSize = 'PAGE_SIZE';
			cookieSearchPage = 'PAGE_SEARCH';
			
			//if cookie not set put the default value which is '10'     
            PAGESIZE = ( $.cookie(cookiePageSize) == null ) ? 10 : $.cookie(cookiePageSize);
         
            //each time user accesses this page it refreshes the expire time of cookie
	        $.cookie(cookiePageSize, PAGESIZE, { path: '/', expires: date });  
            
            
            
			this.defaults = {
				size: PAGESIZE, //
				offset: 0,
				page: 0,
				totalRows: 0,
				totalPages: 0,
				container: null,
				cssNext: '.next',
				cssPrev: '.prev',
				cssFirst: '.first',
				cssLast: '.last',
				cssPageDisplay: '.pagedisplay',
				cssPageSize: '.pagesize',
				seperator: "/",
				positionFixed: true,
				appender: this.appender
			};
			
			this.construct = function(settings) {
				
				return this.each(function() {	
					
					config = $.extend(this.config, $.tablesorterPager.defaults, settings);
					
					var table = this, pager = config.container;
				
					$(this).trigger("appendCache");
					
					config.size = parseInt($(".pagesize",pager).val());
					
					$(config.cssFirst,pager).click(function() {
						moveToFirstPage(table);
						return false;
					});					
					$(config.cssNext,pager).click(function() {
						moveToNextPage(table);
						return false;
					});
					$(config.cssPrev,pager).click(function() {
						moveToPrevPage(table);
						return false;
					});
					$(config.cssLast,pager).click(function() {
						moveToLastPage(table);
						return false;
					});
					$(config.cssPageSize,pager).change(function() {
						setPageSize(table,parseInt($(this).val()));
						return false;
					});	
					
					//Added by Kamal Soni to bind arrow keys to functions
				    $(document).bind('keydown', 'right', function (evt) {
                        moveToNextPage(table);
                        return false;
                    });		
                    $(document).bind('keydown', 'left', function (evt) {
                        moveToPrevPage(table);
                        return false;
                    });
                    $(document).bind('keydown', 'Ctrl+right', function (evt) {
                        moveToLastPage(table);
                        return false;
                    });      
                    $(document).bind('keydown', 'Ctrl+left', function (evt) {
                        moveToFirstPage(table);
                        return false;
                    });
                    
                    $('.pagedisplay').focus(function(){
	                    $(document).unbind('keydown', 'right', '');     
	                    $(document).unbind('keydown', 'left', '');
	                    
	                    originalValue = $(this).val();
                        originalValues = originalValue.split(' ');
                        $(document).bind('keydown', 'return', function (evt) {
                           changeValue = $('.pagedisplay').val();
                           changeValues = changeValue.split(' ');
                           newPageNo = parseInt(changeValues[1]);
                           jqueryPageNo = newPageNo - 1;
                           oldTotalNoOfPages = parseInt(originalValues[3]);
    
                           if(changeValues[0] != originalValues[0] || changeValues[2] != originalValues[2] 
                               || changeValues[3] != originalValues[3] || changeValues[1] == '' || isNaN(newPageNo) 
                               || newPageNo > oldTotalNoOfPages || jqueryPageNo < 0) {
                               $('.pagedisplay').val(originalValue);       
                           } else {       
                        	   originalValue = $('.pagedisplay').val();
                               moveToThisPage(table, jqueryPageNo);
                               return false;                           
                           } 
                       }); 
                    });	
                    for(var unbindCount = 0; unbindCount < unBindArrowKeysFor.length; unbindCount++) {
                    	$(unBindArrowKeysFor[unbindCount]).focus(function(){
	                        $(document).unbind('keydown', 'right', '');     
	                        $(document).unbind('keydown', 'left', '');
                    	});
	                    $(unBindArrowKeysFor[unbindCount]).blur(function () {
	                    	
	                        $(document).bind('keydown', 'right', function (evt) {
	                            moveToNextPage(table);
	                            return false;
	                        });     
	                        $(document).bind('keydown', 'left', function (evt) {
	                            moveToPrevPage(table);
	                            return false;
	                        });
	                    });
                    }
                    $('.pagedisplay').blur(function () {
                    	$(document).unbind('keydown', 'return','');
	                    $(document).bind('keydown', 'right', function (evt) {
	                        moveToNextPage(table);
	                        return false;
	                    });     
	                    $(document).bind('keydown', 'left', function (evt) {
	                        moveToPrevPage(table);
	                        return false;
	                    });
				    });
                                         
                    if ($.cookie(cookieSearchPage) != null ) {
                        moveToThisPage(table,$.cookie(cookieSearchPage));
                    }
                    //when the user changes the pagination store cookie
					$(".pagesize").change( function() {
                        val = $(this).val();
                        $('.pagesize').each(function() {
                        	$(this).val(val);
                        });
                        //If pagination selected is 'All' store '5000' instead of 'All'
                        if(val > 50) {
                        	val = '50000';
                        }
                        //Create cookie
                        $.cookie(cookiePageSize, val, { path: '/', expires: date });   
                    });
                    
				});
			};
			
		}
	});
	// extend plugin scope
	$.fn.extend({
        tablesorterPager: $.tablesorterPager.construct
	});
})(jQuery);
