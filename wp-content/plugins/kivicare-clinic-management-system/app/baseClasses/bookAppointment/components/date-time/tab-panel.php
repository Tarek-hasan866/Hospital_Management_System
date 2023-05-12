<div>
    <div class="iq-kivi-tab-panel-title-animation">
        <h3 class="iq-kivi-tab-panel-title"> <?php echo esc_html__('Select Date and Time', 'kc-lang' ); ?> </h3>
    </div>
    <hr>
</div>
<div class="widget-content">    
    <div class="d-grid grid-template-2 card-list-data iq-kivi-calendar-slot" id="datepicker-grid">
        <input type="hidden" class="inline-flatpicker iq-inline-datepicker d-none" style="display:none">
        <div class="time-slots" id="time-slot">
            <div class="iq-card iq-bg-primary-light text-center" style="min-height: 100%; height:400px">
                <h5 id="selectedDate" name="selectedDate">
                    <?php echo esc_html__('Available time slots', 'kc-lang'); ?>
                </h5>
                <div class="grid-template-3 iq-calendar-card" id="timeSlotLists" name="timeSlotLists" style="height:100%">

                    <p class="loader-class"><?php echo esc_html__('Please Select Date','kc-lang');?></p>
                </div>
            </div>
        </div>
        <span class="d-none" id="selectedAppointmentDate">
        </span>
    </div>
    <div class="doctor-session-error loader-class">
        <p class="">
            <?php echo esc_html__('Select doctor session is not available with selected clinic, please select other doctor or other clinic','kc-lang'); ?>
        </p>
    </div>
    <span class="loader-class doctor-session-loader" id="doctor_loader">
        <?php  if(isLoaderCustomUrl()){?>
            <img src="<?php echo esc_url(kcAppointmentWidgetLoader()); ?>">
        
        <?php }else{            ?>   
            <div class="double-lines-spinner"></div>
        <?php } ?>
    </span>
</div>
