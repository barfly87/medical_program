function setCompassSort(sortList) {
	var sort = sortList.toString();
	var splitSort = sort.split(',');
	var sortArr = new Array;	
	for(var x=0; x< splitSort.length; x+=2) {
		string = '['+ splitSort[x] +','+splitSort[x+1]+']';
		sortArr.push(string);
	}
    var date = new Date();
    date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));         
    $.cookie(cookieSearchSortOrder, sortArr.toString(), { path: '/', expires: date });
}