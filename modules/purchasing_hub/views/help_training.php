<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="purchasing-help sc-ph-guide">
  <div class="ph-guide-hero">
    <div>
      <h2><i class="fa fa-graduation-cap"></i> Purchasing Hub How To</h2>
      <p>A beginner-friendly guide for vendors, purchase orders, vendor quotes, contracts, accounts payable, project purchasing, reports, PDFs, and email workflows.</p>
    </div>
    <div class="ph-guide-badge">Smart Choice Standard</div>
  </div>

  <div class="ph-guide-card warning">
    <h3><i class="fa fa-info-circle"></i> What Purchasing Means</h3>
    <p>Purchasing is the process of buying materials, equipment, tools, rentals, and subcontracted services for a project. The Purchasing Hub keeps vendor purchases separate from customer sales. Customer estimates and invoices record what the customer will pay. Purchase orders and vendor bills record what Smart Choice Contractors USA will buy and owe.</p>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="ph-guide-card">
        <h3><i class="fa fa-file-text-o"></i> What Is a Purchase Order?</h3>
        <p>A purchase order, commonly called a PO, is a formal document sent to a vendor. It tells the vendor exactly what the company authorizes them to supply.</p>
        <ul>
          <li>It identifies the vendor and project.</li>
          <li>It lists the approved products, quantities, prices, and delivery instructions.</li>
          <li>It provides a written purchasing record before the vendor invoice arrives.</li>
          <li>It helps prevent unauthorized purchases, incorrect quantities, and pricing disputes.</li>
        </ul>
      </div>
    </div>
    <div class="col-md-6">
      <div class="ph-guide-card orange">
        <h3><i class="fa fa-random"></i> Basic Purchasing Workflow</h3>
        <ol>
          <li>Create or select the vendor.</li>
          <li>Create or select the material or service item.</li>
          <li>Request or record a vendor quote when price comparison is needed.</li>
          <li>Create the purchase order after approval.</li>
          <li>Email the PO and attachments to the vendor.</li>
          <li>Confirm delivery, pickup, substitutions, and quantities.</li>
          <li>Record the vendor bill under Accounts Payable.</li>
          <li>Mark the bill paid when payment is completed.</li>
          <li>Review project cost and vendor reports.</li>
        </ol>
      </div>
    </div>
  </div>

  <div class="ph-guide-card">
    <h3><i class="fa fa-list"></i> How to Create a Purchase Order</h3>
    <table class="table table-bordered ph-guide-table">
      <thead><tr><th>Step</th><th>What to Enter</th><th>Example</th></tr></thead>
      <tbody>
        <tr><td>1</td><td>Select the vendor.</td><td>Home Depot Pro Desk, ABC Plumbing Supply, local lumber yard.</td></tr>
        <tr><td>2</td><td>Select the project and customer when applicable.</td><td>Smith Bathroom Remodel.</td></tr>
        <tr><td>3</td><td>Enter the order date and expected delivery or pickup date.</td><td>Order July 16; deliver July 18.</td></tr>
        <tr><td>4</td><td>Add every item with SKU, description, quantity, unit, and price.</td><td>12 sheets of 1/2-inch drywall at $15.98 each.</td></tr>
        <tr><td>5</td><td>Add tax, delivery, discounts, and other charges when applicable.</td><td>$75 delivery fee.</td></tr>
        <tr><td>6</td><td>Add clear vendor instructions.</td><td>No substitutions without written approval. Call before delivery.</td></tr>
        <tr><td>7</td><td>Attach plans, specifications, selections, or the vendor quote.</td><td>Cabinet layout PDF and approved finish sheet.</td></tr>
        <tr><td>8</td><td>Review totals, approve, save, generate PDF, and email the vendor.</td><td>PO-2026-0041 emailed to supplier.</td></tr>
      </tbody>
    </table>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="ph-guide-card green">
        <h3><i class="fa fa-comments"></i> Vendor Quotes</h3>
        <p>A vendor quote is a price offer received before the company buys. Use quotes to compare price, lead time, delivery, availability, warranty terms, and substitutions.</p>
        <p><strong>Example:</strong> Request the same roofing material list from three suppliers. Record each quote, compare totals and delivery dates, then convert the approved quote into a purchase order.</p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="ph-guide-card blue">
        <h3><i class="fa fa-money"></i> Accounts Payable</h3>
        <p>Accounts Payable tracks money owed to vendors. After the material or service is supplied, enter the vendor bill, due date, related PO, project, and payment status.</p>
        <p><strong>Do not confuse this with customer invoices.</strong> Customer invoices are money customers owe the company. Vendor bills are money the company owes vendors.</p>
      </div>
    </div>
  </div>

  <div class="ph-guide-card">
    <h3><i class="fa fa-file-text"></i> How to Create a Purchasing Contract</h3>
    <p>A purchasing contract is used when the vendor or subcontractor relationship requires more than a simple purchase order. It can define scope, pricing, schedule, insurance, payment conditions, delivery responsibilities, change approval, and dispute terms.</p>
    <table class="table table-striped ph-guide-table">
      <thead><tr><th>Contract Section</th><th>What It Should Explain</th><th>Sample Wording</th></tr></thead>
      <tbody>
        <tr><td>Parties</td><td>Who is entering the agreement.</td><td>Smart Choice Contractors USA and ABC Cabinet Supply.</td></tr>
        <tr><td>Scope</td><td>What products or services are included.</td><td>Supply and deliver the cabinets listed in approved drawing revision 3.</td></tr>
        <tr><td>Price</td><td>Contract total and approved charges.</td><td>Total contract price: $18,500 plus approved sales tax.</td></tr>
        <tr><td>Payment</td><td>Deposit, progress payments, and final payment.</td><td>30% deposit, 40% upon fabrication, 30% after delivery and inspection.</td></tr>
        <tr><td>Schedule</td><td>Order, production, delivery, and completion dates.</td><td>Delivery required no later than August 20, 2026.</td></tr>
        <tr><td>Changes</td><td>How substitutions or extra charges are approved.</td><td>No substitutions or added charges without written approval.</td></tr>
        <tr><td>Responsibilities</td><td>Who handles measurements, unloading, storage, permits, or installation.</td><td>Vendor is responsible for protected delivery to the project address.</td></tr>
        <tr><td>Acceptance</td><td>How work or materials are inspected and accepted.</td><td>Final payment follows delivery inspection and correction of shortages or damage.</td></tr>
      </tbody>
    </table>
  </div>

  <div class="ph-guide-card orange">
    <h3><i class="fa fa-copy"></i> Sample Contract Workflow</h3>
    <ol>
      <li>Open <strong>Contract Templates</strong> and create a reusable vendor or subcontract agreement.</li>
      <li>Add standard company terms, delivery rules, payment conditions, insurance requirements, and approval language.</li>
      <li>Open <strong>Contracts</strong> and create a contract from the template.</li>
      <li>Select the vendor, project, start date, end date, and contract value.</li>
      <li>Replace template placeholders with the actual scope, products, schedule, and price.</li>
      <li>Attach drawings, specifications, quote documents, insurance certificates, or product schedules.</li>
      <li>Preview the contract, generate the PDF, and email it using the CRM email workflow.</li>
      <li>Keep the approved document connected to the vendor and project for future review.</li>
    </ol>
  </div>

  <div class="ph-guide-card">
    <h3><i class="fa fa-file-o"></i> Purchase Order Example</h3>
    <table class="table table-bordered ph-guide-table">
      <tbody>
        <tr><th>Vendor</th><td>ABC Plumbing Supply</td></tr>
        <tr><th>Project</th><td>Johnson Master Bathroom Remodel</td></tr>
        <tr><th>Items</th><td>1 shower valve, 2 shutoff valves, 1 drain assembly, 60 LF PEX tubing</td></tr>
        <tr><th>Delivery</th><td>Deliver to project by Friday between 8:00 AM and 12:00 PM</td></tr>
        <tr><th>Instructions</th><td>No substitutions. Confirm model numbers before shipment. Call project manager before delivery.</td></tr>
        <tr><th>Attachments</th><td>Fixture schedule and approved customer selection sheet</td></tr>
      </tbody>
    </table>
  </div>

  <div class="ph-guide-card">
    <h3><i class="fa fa-bar-chart"></i> Reports</h3>
    <p>Use reports to review purchase orders, vendor balances, unpaid bills, material costs, project purchasing totals, and vendor activity. Purchasing reports should read CRM sales information without changing the native Estimates, Proposals, Invoices, Customers, or Items tables.</p>
  </div>

  <div class="ph-guide-card warning">
    <h3><i class="fa fa-check-circle"></i> Required Operating Rules</h3>
    <ul>
      <li>Verify vendor, SKU, model, size, color, quantity, price, and delivery date before sending a PO.</li>
      <li>Connect purchases to the correct project whenever the cost belongs to a job.</li>
      <li>Use written instructions for substitutions, delivery access, pickup authorization, and damaged items.</li>
      <li>Attach the approved quote, drawings, specifications, and customer selections when relevant.</li>
      <li>Do not record vendor bills as customer invoices.</li>
      <li>Do not mark a vendor bill paid until payment is confirmed.</li>
      <li>Use contract templates for recurring vendor and subcontract terms.</li>
    </ul>
  </div>
</div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
