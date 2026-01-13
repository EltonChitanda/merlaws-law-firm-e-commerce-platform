# Plan: Implement Automatic Invoice Creation for Case Fees

Here is the step-by-step plan to activate the existing "Compensation Management" feature by building its backend logic.

### Step 1: Fetching Active Cases for the Form

I will create a new API endpoint that the admin form can use to populate the case selection dropdown with real data instead of the current placeholder data.

-   **File to Create:** `app/api/get_cases.php`
-   **Logic:** This script will query the database for all active cases and return them as a JSON object, so the dropdown in the finance page can show a real-time list of cases.

### Step 2: Implementing the Backend Fee Calculation

I will create the backend script that the admin form will submit to. This script will contain the core logic for calculating the fee and creating the invoice.

-   **File to Create:** `app/admin/create-fee.php`
-   **Logic:**

    1.  Receive the `case_id` and `total_won_amount` from the form submission.
    2.  Perform security checks to ensure the user is an authorized admin.
    3.  Calculate the 25% fee amount.
    4.  Generate a unique `invoice_number` and set a `due_date`.
    5.  Execute an `INSERT` query into the `invoices` table, correctly mapping the data to the columns (`case_id`, `amount`, `status` as 'pending', etc.).
    6.  Return a JSON success or error message.

### Step 3: Displaying the Invoice to the Client

I will add a new section to the client's case view page to display any outstanding invoices related to their case, along with a "Pay Now" button.

-   **File to Modify:** `app/cases/view.php`
-   **Changes:**

    1.  I will add a PHP function to fetch all invoices associated with the current `case_id`.
    2.  I will create a new "Invoices" card on the page.
    3.  If any unpaid invoices exist, they will be listed in this card, each with its amount, due date, status, and a "Pay Now" button that links to your existing Payfast payment flow.

### Step 4: Activating the Admin Finance Page

I will modify the existing JavaScript on the finance page to connect it to the new backend scripts.

-   **File to Modify:** `app/admin/finance.php`
-   **Changes:**

    1.  I will update the `loadCasesForCompensation()` JavaScript function to make a `fetch` call to the new `app/api/get_cases.php` endpoint and populate the dropdown with the results.
    2.  I will modify the `submitCompensation()` JavaScript function to send the form data via a `fetch` call to the new `app/admin/create-fee.php` script, replacing the current `setTimeout` simulation.
    3.  The function will then handle the JSON response, showing a success or error message to the admin.

This plan will complete the feature you requested by building the necessary backend logic and connecting it to the existing, well-designed user interface. The rest of your Payfast payment system will work seamlessly with the newly created invoices.

### Step 5: Sidebar Navigation Review and Alignment

I will review and, if needed, update the client sidebar menu (in `include/header.php`) to make sure the "Invoices & Payments" link as well as other finance/payment-related links clearly direct users to a relevant page (e.g., client list of unpaid invoices or cases with quick access to payment actions).

This ensures clients always have a clear pathway to action any outstanding invoices, making the payment flow easy and accessible.