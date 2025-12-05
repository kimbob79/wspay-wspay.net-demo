			</div>
		</main>
	</div>
</div>


<script>
	$(function() {

		$("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

		$('#payments').change(function(){
			var payments = $(this).val();
			if(payments == 'k1') { // 코페이 선택
				$("#payments_open").show();
				$("#installment option").remove();
				$('#installment').append('<?php echo $k1_hal; ?>');
			} else if(payments == 'danal') { // 다날 선택
				$("#payments_open").show();
				$("#installment option").remove();
				$('#installment').append('<?php echo $danal_hal; ?>');
			} else if(payments == 'welcom') { // 다날 선택
				$("#payments_open").show();
				$("#installment option").remove();
				$('#installment').append('<?php echo $welcom_hal; ?>');
			} else if(payments == 'paysis') { // 다날 선택
				$("#payments_open").show();
				$("#installment option").remove();
				$('#installment').append('<?php echo $paysis_hal; ?>');
			} else {
				$("#payment_open").hide();
			}
		});
	});


</script>
</body>
</html>
