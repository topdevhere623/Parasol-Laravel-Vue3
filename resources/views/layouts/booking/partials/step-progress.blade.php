<ul class="booking-step-progress">
  <li @class(['current' => $step == 1])>
    <span>1</span>
    <div class="ms-2">
      <h6>YOUR PACKAGE</h6>
      <p>Finetune your selection</p>
    </div>
  </li>
  <li @class(['current' => $step == 2])>
    <span>2</span>
    <div class="ms-2">
      <h6>PAYMENT</h6>
      <p>Review & Purchase</p>
    </div>
  </li>
  <li @class(['current' => $step == 3])>
    <span>3</span>
    <div class="ms-2">
      <h6>MEMBER DETAILS</h6>
      <p>Complete membership information</p>
    </div>
  </li>
</ul>
