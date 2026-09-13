$(function () {
	replaceHash()
	// initSidebar()
	if ($("#stockPerformanceChart").length > 0) {
		stockPerformanceChart()
	}
	if ($("#stockOverviewChart").length > 0) {
		stockOverviewChart()
	}
	if ($("#orderSummaryChart").length > 0) {
		orderSummaryChart()
	}
	if ($("#salesTable").length > 0) {
		initSalesTable()
	}
	if ($("#productsTable").length > 0) {
		initProductsTable()
	}
	if ($("#categoryTable").length > 0) {
		initCategoryTable()
	}
	if ($("#subCategoryTable").length > 0) {
		initSubCategoryTable()
	}
	if ($("#financialTable").length > 0) {
		initFinancialTable()
	}
	if ($("#cashbookTable").length > 0) {
		initCashbookTable()
	}
	if ($("#expenseTable").length > 0) {
		initExpenseTable()
	}
	if ($("#payrollTable").length > 0) {
		initPayrollTable()
	}
	if ($("#loansTable").length > 0) {
		initLoansTable()
	}
	if ($("#taxesTable").length > 0) {
		initTaxesTable()
	}
	if ($("#measurementUnitTable").length > 0) {
		initMeasurementUnitTable()
	}
	if ($("#wareHouseTable").length > 0) {
		initWareHouseTable()
	}
	if ($("#discountTable").length > 0) {
		initDiscountTable()
	}
	if ($("#customerTable").length > 0) {
		initCustomerTable()
	}
})

function shouldSkipDataTable(selector) {
	return $(selector).is('[data-laravel-pagination="true"]');
}

// Initialize Sidebar
// function initSidebar() {
// 	setActiveSidebarLink();

// 	// Handle collapse toggle icon rotation for Accounts
// 	$('#accountsSubmenu').on('show.bs.collapse', function () {
// 		$('.dashboard-sidebar__toggle[data-bs-target="#accountsSubmenu"]').attr('aria-expanded', 'true');
// 	});

// 	$('#accountsSubmenu').on('hide.bs.collapse', function () {
// 		$('.dashboard-sidebar__toggle[data-bs-target="#accountsSubmenu"]').attr('aria-expanded', 'false');
// 	});

// 	// Handle collapse toggle icon rotation and active state for Settings
// 	$('#settingsSubmenu').on('show.bs.collapse', function () {
// 		$('.dashboard-sidebar__toggle[data-bs-target="#settingsSubmenu"]').attr('aria-expanded', 'true');
// 		// Add active class to Settings link when dropdown opens
// 		$('.settings-link-toggle').addClass('active');
// 	});

// 	$('#settingsSubmenu').on('hide.bs.collapse', function () {
// 		$('.dashboard-sidebar__toggle[data-bs-target="#settingsSubmenu"]').attr('aria-expanded', 'false');
// 		// Only remove active if not on any settings-related page
// 		const currentPage = window.location.pathname.split('/').pop() || 'index.php';
// 		const settingsPages = ['settings.php', 'system-settings.php', 'taxes.php', 'measurement-unit.php', 'ware-house.php', 'discount.php', 'vendor.php', 'customer.php'];
// 		if (!settingsPages.includes(currentPage)) {
// 			$('.settings-link-toggle').removeClass('active');
// 		}
// 	});

// 	// Handle Settings link click to toggle dropdown
// 	$(document).on('click', '.settings-link-toggle', function (e) {
// 		e.preventDefault();
// 		e.stopPropagation();
// 		const toggleBtn = $('.dashboard-sidebar__toggle[data-bs-target="#settingsSubmenu"]');
// 		if (toggleBtn.length) {
// 			// Manually toggle the collapse by clicking the toggle button
// 			toggleBtn[0].click();
// 		}
// 	});
// }

// function setActiveSidebarLink() {
// 	// Get current page filename
// 	const currentPage = window.location.pathname.split('/').pop() || 'index.php';

// 	// Remove active class from all links
// 	$('.dashboard-sidebar__list a').removeClass('active');
// 	$('.dashboard-sidebar__submenu-link').removeClass('active');

// 	// Check for settings.php page first
// 	if (currentPage === 'settings.php') {
// 		// Highlight settings parent link
// 		$('.settings-link-toggle').addClass('active');
// 		// Don't add active class to General Settings submenu link for settings.php
// 		// Don't auto-expand dropdown for settings.php page itself
// 		return; // Exit early to prevent accounts logic from running
// 	}

// 	// Check for system-settings.php
// 	if (currentPage === 'system-settings.php') {
// 		// Highlight settings parent link
// 		$('.settings-link-toggle').addClass('active');

// 		// Expand settings submenu
// 		const settingsSubmenu = document.getElementById('settingsSubmenu');
// 		if (settingsSubmenu) {
// 			const bsCollapse = new bootstrap.Collapse(settingsSubmenu, {
// 				toggle: false
// 			});
// 			bsCollapse.show();
// 		}

// 		// Highlight System Settings submenu link
// 		$('.dashboard-sidebar__submenu-link[href="system-settings.php"]').addClass('active');
// 		return; // Exit early to prevent accounts logic from running
// 	}

// 	// Check for other settings pages (taxes, measurement-unit, etc.)
// 	const settingsPages = ['taxes.php', 'measurement-unit.php', 'ware-house.php', 'discount.php', 'vendor.php', 'customer.php'];
// 	const addSettingsPages = ['add-taxes.php', 'add-measurement.php', 'add-ware-house.php', 'add-discount.php', 'add-vendor.php', 'add-customer.php'];
	
// 	if (settingsPages.includes(currentPage) || addSettingsPages.includes(currentPage)) {
// 		// Highlight settings parent link
// 		$('.settings-link-toggle').addClass('active');

// 		// Expand settings submenu
// 		const settingsSubmenu = document.getElementById('settingsSubmenu');
// 		if (settingsSubmenu) {
// 			const bsCollapse = new bootstrap.Collapse(settingsSubmenu, {
// 				toggle: false
// 			});
// 			bsCollapse.show();
// 		}

// 		// Highlight general settings submenu link (it has href="taxes.php" in header)
// 		$('.dashboard-sidebar__submenu-link[href="taxes.php"]').addClass('active');
// 		return; // Exit early to prevent accounts logic from running
// 	}

// 	// Find and add active class to current page link
// 	$('.dashboard-sidebar__list a, .dashboard-sidebar__submenu-link').each(function () {
// 		const href = $(this).attr('href');
// 		// Only match exact href, not includes (to avoid false matches)
// 		if (href && href === currentPage) {
// 			$(this).addClass('active');

// 			// If it's a submenu link, check which parent it belongs to
// 			const $submenuLink = $(this);
// 			if ($submenuLink.hasClass('dashboard-sidebar__submenu-link')) {
// 				// Check if it's in accounts submenu
// 				if ($submenuLink.closest('#accountsSubmenu').length > 0) {
// 					// Expand accounts submenu
// 					const accountsSubmenu = document.getElementById('accountsSubmenu');
// 					if (accountsSubmenu) {
// 						const bsCollapse = new bootstrap.Collapse(accountsSubmenu, {
// 							toggle: false
// 						});
// 						bsCollapse.show();
// 					}

// 					// Highlight accounts parent link
// 					$('.dashboard-sidebar__link[href="accounts.php"]').addClass('active');
// 				}
// 				// Check if it's in settings submenu
// 				else if ($submenuLink.closest('#settingsSubmenu').length > 0) {
// 					// Expand settings submenu
// 					const settingsSubmenu = document.getElementById('settingsSubmenu');
// 					if (settingsSubmenu) {
// 						const bsCollapse = new bootstrap.Collapse(settingsSubmenu, {
// 							toggle: false
// 						});
// 						bsCollapse.show();
// 					}

// 					// Highlight settings parent link
// 					$('.dashboard-sidebar__link[href="settings.php"]').addClass('active');
// 				}
// 			}
// 		}
// 	});

// 	// Special handling for accounts.php page
// 	if (currentPage === 'accounts.php') {
// 		$('.dashboard-sidebar__link[href="accounts.php"]').addClass('active');
// 		// Don't auto-expand dropdown for accounts.php page itself
// 		return; // Exit early
// 	}

// 	// Check for accounts sub-pages (but exclude settings pages)
// 	const accountsSubPages = ['cashbook.php', 'expense.php', 'expenses.php', 'payroll.php', 'payroll-process.php', 'loan-and-advances.php', 'grant-new-loan-and-advance.php', 'profit-report.php', 'loss-report.php', 'revenue-report.php', 'new-cashbook-transaction.php'];
// 	const isAccountsSubPage = accountsSubPages.includes(currentPage) ||
// 		(currentPage.includes('expense') && !currentPage.includes('settings')) ||
// 		(currentPage.includes('expenses') && !currentPage.includes('settings')) ||
// 		(currentPage.includes('payroll') && !currentPage.includes('settings')) ||
// 		(currentPage.includes('loan') && !currentPage.includes('settings')) ||
// 		(currentPage.includes('profit') && !currentPage.includes('settings')) ||
// 		(currentPage.includes('loss') && !currentPage.includes('settings')) ||
// 		(currentPage.includes('revenue') && !currentPage.includes('settings')) ||
// 		(currentPage.includes('cashbook') && !currentPage.includes('settings'));

// 	if (isAccountsSubPage) {
// 		// Expand accounts submenu
// 		const accountsSubmenu = document.getElementById('accountsSubmenu');
// 		if (accountsSubmenu) {
// 			const bsCollapse = new bootstrap.Collapse(accountsSubmenu, {
// 				toggle: false
// 			});
// 			bsCollapse.show();
// 		}

// 		// Highlight accounts parent link
// 		$('.dashboard-sidebar__link[href="accounts.php"]').addClass('active');

// 		// If it's cashbook related page, highlight cashbook submenu link
// 		if (currentPage === 'cashbook.php' || currentPage === 'new-cashbook-transaction.php' || (currentPage.includes('cashbook') && !currentPage.includes('settings'))) {
// 			$('.dashboard-sidebar__submenu-link[href="cashbook.php"]').addClass('active');
// 		}

// 		// If it's expense related page, highlight expense submenu link
// 		if (currentPage === 'expense.php' || (currentPage.includes('expense') && !currentPage.includes('settings'))) {
// 			$('.dashboard-sidebar__submenu-link[href="expense.php"]').addClass('active');
// 		}

// 		// If it's payroll related page, highlight payroll submenu link
// 		if (currentPage === 'payroll.php' || currentPage === 'payroll-process.php' || (currentPage.includes('payroll') && !currentPage.includes('settings'))) {
// 			$('.dashboard-sidebar__submenu-link[href="payroll.php"]').addClass('active');
// 		}

// 		// If it's loan related page, highlight loan submenu link
// 		if (currentPage === 'loan-and-advances.php' || currentPage === 'grant-new-loan-and-advance.php' || (currentPage.includes('loan') && !currentPage.includes('settings'))) {
// 			$('.dashboard-sidebar__submenu-link[href="loan-and-advances.php"]').addClass('active');
// 		}

// 		// If it's profit report page
// 		if (currentPage === 'profit-report.php' || (currentPage.includes('profit') && !currentPage.includes('settings'))) {
// 			$('.dashboard-sidebar__submenu-link[href="profit-report.php"]').addClass('active');
// 		}

// 		// If it's loss report page
// 		if (currentPage === 'loss-report.php' || (currentPage.includes('loss') && !currentPage.includes('settings'))) {
// 			$('.dashboard-sidebar__submenu-link[href="loss-report.php"]').addClass('active');
// 		}

// 		// If it's revenue report page
// 		if (currentPage === 'revenue-report.php' || (currentPage.includes('revenue') && !currentPage.includes('settings'))) {
// 			$('.dashboard-sidebar__submenu-link[href="revenue-report.php"]').addClass('active');
// 		}
// 	}
// }

function replaceHash() {
	document.querySelectorAll("a").forEach((a) => {
		let href = a.getAttribute("href");
		a.href = href ?
			href.startsWith("#") && href.endsWith("#") ?
			href.replace("#", "javascript:void(0)") :
			href :
			"javascript:void(0)";
	});
}

// Stock Performance Chart (Bar Chart)
function stockPerformanceChart() {
	const stockPerformanceCtx = document.getElementById('stockPerformanceChart').getContext('2d');
	const stockPerformanceChart = new Chart(stockPerformanceCtx, {
		type: 'bar',
		data: {
			labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
			datasets: [{
				label: 'Last Week',
				data: [6, 5, 3, 4, 2, 7, 5],
				backgroundColor: '#70504B',
				borderRadius: 10
			}, {
				label: 'This Week',
				data: [9, 4, 4, 5, 3, 6, 7],
				backgroundColor: '#FE9874',
				borderRadius: 10
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: {
					display: false
				},
				tooltip: {
					enabled: true
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					max: 10,
					ticks: {
						stepSize: 2,
						font: {
							size: 11
						},
						color: '#999'
					},
					grid: {
						color: '#F5F5F5',
						drawBorder: false
					}
				},
				x: {
					grid: {
						display: false,
						drawBorder: false
					},
					ticks: {
						font: {
							size: 11
						},
						color: '#666'
					}
				}
			}
		}
	});
}

// Stock Overview Chart (Doughnut Chart)
function stockOverviewChart() {
	const stockOverviewCtx = document.getElementById('stockOverviewChart').getContext('2d');
	const stockOverviewChart = new Chart(stockOverviewCtx, {
		type: 'doughnut',
		data: {
			labels: ['Mobile', 'Web', 'Other'],
			datasets: [{
				data: [40, 10, 50],
				backgroundColor: ['#FE9874', '#DDCFCC', '#70504B'],
				borderWidth: 0,
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: {
					display: false
				},
				tooltip: {
					enabled: true
				}
			},
			cutout: '70%'
		}
	});
}

// Order Summary Chart (Line Chart)
function orderSummaryChart() {
	const orderSummaryCtx = document.getElementById('orderSummaryChart').getContext('2d');
	const orderSummaryChart = new Chart(orderSummaryCtx, {
		type: 'line',
		data: {
			labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
			datasets: [{
				label: 'Last Month',
				data: [2, 3, 7, 4, 5, 3, 8, 4, 5, 3, 4],
				borderColor: '#70504B',
				backgroundColor: 'rgba(112, 80, 75, 0.1)',
				fill: true,
				tension: 0.4,
				pointRadius: 0,
				pointHoverRadius: 5
			}, {
				label: 'This Month',
				data: [1.5, 2.5, 6.5, 3.5, 4.5, 2.5, 1.7, 3.5, 4.5, 2.5, 3.5],
				borderColor: '#FE9874',
				backgroundColor: 'rgba(254, 152, 116, 0.1)',
				fill: true,
				tension: 0.4,
				pointRadius: 0,
				pointHoverRadius: 5
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: {
					display: false
				},
				tooltip: {
					enabled: true,
					mode: 'index',
					intersect: false
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					max: 10,
					ticks: {
						stepSize: 2.5,
						font: {
							size: 11
						},
						color: '#999'
					},
					grid: {
						color: '#F5F5F5',
						drawBorder: false
					}
				},
				x: {
					grid: {
						display: false,
						drawBorder: false
					},
					ticks: {
						maxRotation: 0,
						font: {
							size: 11
						},
						color: '#666'
					}
				}
			},
			interaction: {
				mode: 'index',
				intersect: false
			}
		}
	});
}

// Initialize Sales Table with DataTables
function initSalesTable() {
	if (shouldSkipDataTable('#salesTable')) {
		return;
	}
	$('#salesTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Products Table with DataTables
function initProductsTable() {
	if (shouldSkipDataTable('#productsTable')) {
		return;
	}
	$('#productsTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Category Table with DataTables
function initCategoryTable() {
	if (shouldSkipDataTable('#categoryTable')) {
		return;
	}
	$('#categoryTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Sub Category Table with DataTables
function initSubCategoryTable() {
	$('#subCategoryTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Financial Table with DataTables
function initFinancialTable() {
	$('#financialTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Cashbook Table with DataTables
function initCashbookTable() {
	if (shouldSkipDataTable('#cashbookTable')) {
		return;
	}
	$('#cashbookTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		},
		footerCallback: function (row, data, start, end, display) {
			// Keep footer visible
		}
	});
}

// Initialize Expense Table with DataTables
function initExpenseTable() {
	if (shouldSkipDataTable('#expenseTable')) {
		return;
	}
	$('#expenseTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Payroll Table with DataTables
function initPayrollTable() {
	if (shouldSkipDataTable('#payrollTable')) {
		return;
	}
	$('#payrollTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Loans Table with DataTables
function initLoansTable() {
	// Remove progress rows temporarily to avoid colspan issues
	var progressRows = [];
	$('#loansTable tbody tr.loan-progress-row').each(function () {
		var progressRow = $(this);
		var employeeName = progressRow.attr('data-for-employee');
		progressRows.push({
			row: progressRow.clone(true),
			employeeName: employeeName
		});
		progressRow.remove();
	});

	var table = $('#loansTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		},
		drawCallback: function (settings) {
			// Re-insert progress rows after their corresponding data rows
			var tbody = $('#loansTable tbody');
			progressRows.forEach(function (progressData) {
				// Find the data row with matching employee name
				var dataRow = tbody.find('tr[data-employee="' + progressData.employeeName + '"]');
				if (dataRow.length && !dataRow.next().hasClass('loan-progress-row')) {
					dataRow.after(progressData.row.clone(true));
				}
			});
		}
	});
}

// Initialize Taxes Table with DataTables
function initTaxesTable() {
	$('#taxesTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Measurement Unit Table with DataTables
function initMeasurementUnitTable() {
	$('#measurementUnitTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Ware House Table with DataTables
function initWareHouseTable() {
	$('#wareHouseTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Discount Table with DataTables
function initDiscountTable() {
	$('#discountTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

// Initialize Customer Table with DataTables
function initCustomerTable() {
	$('#customerTable').DataTable({
		responsive: true,
		pageLength: 10,
		lengthMenu: [
			[10, 25, 50, -1],
			[10, 25, 50, "All"]
		],
		order: [
			[0, 'asc']
		],
		language: {
			search: "Search:",
			lengthMenu: "Show _MENU_ entries",
			info: "Showing _START_ to _END_ of _TOTAL_ entries",
			infoEmpty: "Showing 0 to 0 of 0 entries",
			infoFiltered: "(filtered from _MAX_ total entries)",
			paginate: {
				first: "First",
				last: "Last",
				next: "Next",
				previous: "Previous"
			}
		}
	});
}

window.initSelect2Elements = function (scope) {
	const $scope = scope ? $(scope) : $(document);

	$scope.find('.js-select2').each(function () {
		const $select = $(this);

		if ($select.hasClass('select2-hidden-accessible')) {
			return;
		}

		const placeholder =
			$select.data('placeholder') ||
			$select.attr('placeholder') ||
			$select.find('option:first').text() ||
			'Select Option';

		$select.select2({
			placeholder: placeholder,
			allowClear: false,
			width: '100%'
		});
	});
};

$(document).ready(function() {
	window.initSelect2Elements(document);
});
