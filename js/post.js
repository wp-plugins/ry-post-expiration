jQuery(document).ready( function($) {
	var $exptimestampdiv = $('#exptimestampdiv');

	updateExpText = function() {
		if( !$exptimestampdiv.length ) {
			return true;
		}
		var attemptedDate, originalDate;
		var aa = $('#eaa').val(), mm = $('#emm').val(), jj = $('#ejj').val(), hh = $('#ehh').val(), mn = $('#emn').val();

		attemptedDate = new Date( aa, mm - 1, jj, hh, mn );
		originalDate = new Date( $('#hidden_eaa').val(), $('#hidden_emm').val() -1, $('#hidden_ejj').val(), $('#hidden_ehh').val(), $('#hidden_emn').val() );

		if ( attemptedDate.getFullYear() != aa || (1 + attemptedDate.getMonth()) != mm || attemptedDate.getDate() != jj || attemptedDate.getMinutes() != mn ) {
			$exptimestampdiv.find('.timestamp-wrap').addClass('form-invalid');
			return false;
		} else {
			$exptimestampdiv.find('.timestamp-wrap').removeClass('form-invalid');
		}

		if ( originalDate.toUTCString() == attemptedDate.toUTCString() ) {
		} else {
			console.log(2);
			$('#exptimestamp').html(
				postL10n.dateFormat.replace('%1$s', $('option[value="' + $('#emm').val() + '"]', '#emm').text() )
					.replace( '%2$s', jj )
					.replace( '%3$s', aa )
					.replace( '%4$s', hh )
					.replace( '%5$s', mn )
			);
		}

		return true;
	};

	$exptimestampdiv.siblings('a.edit-exptimestamp').click( function( event ) {
		if ( $exptimestampdiv.is( ':hidden' ) ) {
			$exptimestampdiv.slideDown('fast');
			$('#mm').focus();
			$(this).hide();
		}
		event.preventDefault();
	});

	$exptimestampdiv.find('.cancel-exptimestamp').click( function( event ) {
		$exptimestampdiv.slideUp('fast').siblings('a.edit-exptimestamp').show().focus();
		$('#emm').val($('#hidden_emm').val());
		$('#ejj').val($('#hidden_ejj').val());
		$('#eaa').val($('#hidden_eaa').val());
		$('#ehh').val($('#hidden_ehh').val());
		$('#emn').val($('#hidden_emn').val());
		updateExpText();
		event.preventDefault();
	});

	$exptimestampdiv.find('.save-exptimestamp').click( function( event ) {
		if( updateExpText() ) {
			$exptimestampdiv.slideUp('fast');
			$exptimestampdiv.siblings('a.edit-exptimestamp').show();
		}
		event.preventDefault();
	});
});
