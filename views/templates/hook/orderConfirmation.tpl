{* orderConfirmation.tpl
 *
 * Copyright (c) 2026 LinuxISP (Pty) Ltd
 * You (being anyone who is not LinuxISP (Pty) Ltd may download and use this plugin / code in your own website in conjunction with a registered and active Google account. If your Google account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * Grant: “Licensor hereby grants Licensee a non-exclusive, non-transferable, revocable right to use, run, and modify the Object Code and Source Code solely for Licensee’s internal purposes. Licensee may not distribute, transmit, publish, sublicense, rent, lease, sell, or otherwise transfer the Object Code or Source Code or Derivative Works to any third party.”
 * 
 * 
 * @author     Ruben Venter (ruben@linuxweb.co.za)
 * @version    1.0
 * @date       01/27/2026
 *
 * @link       https://github.com/Linuxweb/ThirtyBees_GA4/
 *}

<script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event': 'purchase',
      'transaction_id': '{$order_id}',
      'value': {$value},
      'currency': '{$currency}',
      'items': {$items nofilter}
    });
    console.log('GA4: Purchase event pushed to dataLayer for Order: {$order_id}');
</script>
