<?php

/**
 * @file
 * Contains Fuber taxi booking application's business logic along with a few unit test cases.
 */

class Cab {
	/**
	 * ID that uniquely identifies a cab.
	 * @var int
	 */
	public $cab_id;
	/**
	 * Cab location on coordinate plane.
	 * @var array
	 */
	public $location = array('x' => 0, 'y' => 0);
	/**
	 * Cab color. Special cabs have a special color ('pink' in our case).
	 * @var string
	 */
	public $color;
	/**
	 * Whether the cab has been assigned to a customer or not.
	 * @var bool
	 */
	public $assigned;
	/**
	 * Stores the instances of this class.
	 * @var array
	 */
	public static $instances = array();

	public function __construct($cab_id, $location, $color, $assigned = false) {
		$this->cab_id = $cab_id;
		$this->location = $location;
		$this->color = $color;
		$this->assigned = $assigned;
		self::$instances[] = $this;
	}
}

class Customer {
	/**
	 * ID that uniquely identifies a customer.
	 * @var int
	 */
	public $customer_id;
	/**
	 * Customer's pickup location on the coordinate plane.
	 * @var array
	 */
	public $pickup = array('x' => 0, 'y' => 0);
	/**
	 * Customer's destination on the coordinate plane.
	 * @var array
	 */
	public $destination = array('x' => 0, 'y' => 0);
	/**
	 * Whether the customer is a hipster or not.
	 * Hipster here implies a customer who wants only a special cab.
	 * @var bool
	 */
	public $is_hipster;
	/**
	 * ID of the cab assigned to the customer.
	 * @var int
	 */
	public $assigned_cab_id;

	public function __construct($customer_id, $pickup, $destination, $is_hipster) {
		$this->customer_id = $customer_id;
		$this->pickup = $pickup;
		$this->destination = $destination;
		$this->is_hipster = $is_hipster;
	}
}

/**
 * Assign the nearest cab to the customer if available.
 * @param  Customer $customer 		A Customer ready to book a cab.
 */
function assign_cab(Customer $customer) {
	$nearest_cab = get_nearest_cab($customer);

	if (!isset($nearest_cab->cab_id)) {
		echo "No cabs available right now :(", PHP_EOL;
	}
	else {
		$customer->assigned_cab_id = $nearest_cab->cab_id;
		$nearest_cab->assigned = true;
		echo "Cab with ID '" . $customer->assigned_cab_id . "' has been assigned to you. Enjoy your ride :)", PHP_EOL;
	}
}

/**
 * Check if the assigned cab carrying the customer has arrived at his/her specified destination.
 * @param  Customer $customer 		A Customer who has booked a cab.
 */
function check_destination_reached(Customer $customer) {
	if (isset($customer->assigned_cab_id)) {
		$assigned_cab = get_cab_by_id($customer->assigned_cab_id);

		if (isset($customer->destination['x']) && $customer->destination['y']) {
			if (($customer->destination['x'] == $assigned_cab->location['x']) && ($customer->destination['y'] == $assigned_cab->location['y'])) {
				$assigned_cab->assigned = false;
				unset($customer->assigned_cab_id);
				echo "Destination reached!", PHP_EOL;
			}
		}
		else {
			echo "Please specify the destination.", PHP_EOL;
		}
	}
}

/**
 * Returns the Cab object by its cab_id.
 * @param  int $cab_id 				The ID of a cab.
 * @return  Cab 					 		Cab object retrieved by its id.
 */
function get_cab_by_id($cab_id) {
	$cab_by_id = new stdClass();

	foreach (get_all_cabs() as $cab) {
		if ($cab->cab_id == $cab_id) {
			$cab_by_id = $cab;
		}
	}

	return $cab_by_id;
}

/**
 * Find the nearest available cab to a customer.
 * @param  Customer $customer 		A Customer ready to book a cab.
 * @return Cab 						        Cab object with the nearest distance to customer.
 */
function get_nearest_cab(Customer $customer) {
	$shortest_distance = 99999999;
	$bookable_cabs = array();
	$nearest_cab = new stdClass();

	if (isset($customer->is_hipster) && $customer->is_hipster) {
		$bookable_cabs = get_special_cabs();
	}
	else {
		$bookable_cabs = get_available_cabs();
	}

	if (!empty($bookable_cabs)) {
		foreach ($bookable_cabs as $cab) {
			$horizontal_displacement = $customer->pickup['x'] - $cab->location['x'];
			$vertical_displacement = $customer->pickup['y'] - $cab->location['y'];
			$distance = sqrt($horizontal_displacement * $horizontal_displacement + $vertical_displacement * $vertical_displacement);

			if ($shortest_distance > $distance) {
				$shortest_distance = $distance;
				$nearest_cab = $cab;
			}
		}
	}

	return $nearest_cab;
}

/**
 * Find all available special cabs for hipster customers.
 * @return array 		An array storing cab objects which are special (i.e. 'pink' in color).
 */
function get_special_cabs() {
	$special_cabs = array();

	foreach (get_available_cabs() as $cab) {
		if (isset($cab->color) && $cab->color == 'pink') {
			$special_cabs[] = $cab;
		}
	}

	return $special_cabs;
}

/**
 * Find all available cabs i.e. the cabs which have not been assigned.
 * @return array 		An array storing cab objects which are available (i.e. not assigned).
 */
function get_available_cabs() {
	$available_cabs = array();

	foreach (get_all_cabs() as $cab) {
		if (isset($cab->assigned) && $cab->assigned == false) {
			$available_cabs[] = $cab;
		}
	}

	return $available_cabs;
}

/**
 * Find all the cabs.
 * @return array 		An array storing all the cab objects.
 */
function get_all_cabs() {
	return Cab::$instances;
}


/**
 * Unit Test Cases
 */

/**
 * Test case 1
 * When customer provides all the information - his pickup location, destination, and if he a hipster or not;
 * plus when a few cabs are available.
 * In this case, the nearest available cab would be assigned to him.
 */

// Cab(cab_id, location, color, assigned[optional])
$cab1 = new Cab(1, array('x' => 20, 'y' => 0), 'red');
$cab2 = new Cab(2, array('x' => 0, 'y' => 10), 'yellow');
$cab3 = new Cab(3, array('x' => 7, 'y' => 24), 'pink');

// Customer(customer_id, pickup, destination, is_hipster)
$customer1 = new Customer(1, array('x' => 0, 'y' => 0), array('x' => 42, 'y' => 11), false);

// Assign a cab to customer from available cabs. We are basically running this on a request (assume a customer has requested a cab).
// Nearest cab: cab_id '2' (distance = 10).
assign_cab($customer1);

// Check if the cab has arrived at customer's destination.
// This check should practically be run as short interval cron task but for the sake of simplicity we check it here itself.
check_destination_reached($customer1);

/**
 * Test case 2
 * When a cab, though nearest to the customer, is already assigned.
 * In this case, the next nearest available cab should be assigned to customer.
 */

$cab4 = new Cab(4, array('x' => 3, 'y' => 4), 'yellow', true);

$customer2 = new Customer(2, array('x' => 0, 'y' => 0), array('x' => 10, 'y' => 20), false);

// Nearest available cab: cab_id '1' (distance = 20) as cab_id '4' and cab_id '2' (nearer ones) are already assigned.
assign_cab($customer2);

check_destination_reached($customer2);

/**
 * Test case 3
 * When a 'hipster' customer requests a cab.
 * In this case, only a special cab should be assigned to him that is nearest and available.
 */

$customer3 = new Customer(3, array('x' => 0, 'y' => 0), array('x' => 0, 'y' => 200), true);

// Nearest available special cab: cab_id '3'.
assign_cab($customer3);

check_destination_reached($customer3);

/**
 * Test case 4
 * In case no cab is available.
 * In this case, appropriate message should be should to customer, rejecting his request.
 */

// Temporarily mark cab_id '4' as assigned.
$cab4->assigned = true;

$customer4 = new Customer(4, array('x' => 0, 'y' => 0), array('x' => 100, 'y' => 0), false);

// No cab available as all are assigned. A message indicating cabs unavailability will be displayed.
assign_cab($customer4);

// This check won't do anything as there are no cabs assigned to customer_id '4'.
check_destination_reached($customer4);

?>

