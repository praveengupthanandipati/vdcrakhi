<?php
$pageTitle = 'FAQs | Vdesiconnect';
require __DIR__ . '/components/header.php';

$faqs = [
    ['q' => 'What is Raksha Bandhan and when is it celebrated?', 'a' => 'Raksha Bandhan is an Indian festival celebrating the bond between brothers and sisters, where sisters tie a Rakhi thread on their brother\'s wrist. It falls on the full moon day (Purnima) of the Hindu month of Shravan, usually in August.'],
    ['q' => 'Do you ship Rakhis to the USA as well as India?', 'a' => 'Yes, we ship across both India and the USA. Simply select your shipping country at checkout and we\'ll take care of the rest.'],
    ['q' => 'How long does delivery take within India?', 'a' => 'Orders within India are typically delivered within 3-5 business days, depending on your location.'],
    ['q' => 'How long does delivery take to the USA?', 'a' => 'International orders to the USA usually take 7-12 business days to arrive. We recommend ordering early during the festival season to avoid delays.'],
    ['q' => 'What payment methods do you accept?', 'a' => 'We accept all major credit/debit cards, UPI, net banking and popular wallets through our secure payment partner, Razorpay.'],
    ['q' => 'Is it safe to pay online on your website?', 'a' => 'Absolutely. All payments are processed through Razorpay\'s encrypted, PCI-DSS compliant checkout — we never store your card details.'],
    ['q' => 'Can I customize my Rakhi order with a name or message?', 'a' => 'Select combo and gift set products support a personalised card or message. Add your request in the order notes and our team will reach out to confirm.'],
    ['q' => 'What categories of Rakhis do you offer?', 'a' => 'Our collection spans Traditional, Kids, Premium & Designer, Eco-Friendly, Return Gift Sets, and Combo Packs (Rakhi + Sweets).'],
    ['q' => 'Are your eco-friendly Rakhis biodegradable?', 'a' => 'Yes, our Eco-Friendly range uses natural materials like rudraksha, tulsi beads, sandalwood, jute and plantable seed paper.'],
    ['q' => 'Do you offer combo packs with sweets?', 'a' => 'Yes, our Combo Packs pair a handpicked Rakhi with festive sweets like Kaju Katli, Soan Papdi, Motichoor Ladoo and more.'],
    ['q' => 'What are Return Gift Sets?', 'a' => 'Return Gift Sets are small tokens of appreciation — think chocolates, candles, photo frames and keychains — that brothers can gift back to their sisters.'],
    ['q' => 'Can I return or exchange a Rakhi after purchase?', 'a' => 'Yes, unused items in original packaging can be returned or exchanged within 7 days of delivery. Personalised items are not eligible for return.'],
    ['q' => 'What is your return/exchange policy timeframe?', 'a' => 'You have 7 days from the date of delivery to request a return or exchange. Refunds are processed within 5-7 business days of approval.'],
    ['q' => 'Will Rakhis reach on time for Raksha Bandhan day?', 'a' => 'We strongly recommend ordering at least 10-12 days before Raksha Bandhan, especially for international USA deliveries, to guarantee on-time arrival.'],
    ['q' => 'Can I place a bulk order for corporate or community gifting?', 'a' => 'Yes, we support bulk and corporate orders. Reach out to us at support@vdesiconnect.com with your requirements and quantity.'],
    ['q' => 'Do you offer Rakhis for kids?', 'a' => 'Yes, our Kids Rakhis collection features fun cartoon, superhero and character designs that children love.'],
    ['q' => 'How do I track my order?', 'a' => 'Once your order ships, you\'ll receive a tracking link via email so you can follow its journey right to your doorstep.'],
    ['q' => 'What if my Rakhi arrives damaged?', 'a' => 'In the rare event your order arrives damaged, contact us within 48 hours with photos and we\'ll arrange a free replacement or refund.'],
    ['q' => 'Do you provide gift wrapping?', 'a' => 'Yes, every order is packed with care in festive gift-ready packaging at no extra cost.'],
    ['q' => 'How can I contact customer support?', 'a' => 'You can reach our support team anytime at support@vdesiconnect.com and we\'ll get back to you within 24 hours.'],
];
?>

<div class="breadcrumb-bar">
  <div class="wrap">
    <a href="index.php" class="text-decoration-none">Home</a> / <span>FAQs</span>
  </div>
</div>

<section class="py-5">
  <div class="wrap">
    <div class="section-title">
      <span>We're Here to Help</span>
      <h2>Frequently Asked Questions</h2>
      <div class="underline"></div>
    </div>

    <div class="faq-wrap mx-auto">
      <div class="accordion" id="faqAccordion">
        <?php foreach ($faqs as $i => $faq): $id = 'faq' . $i; ?>
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id ?>">
              <?= htmlspecialchars($faq['q']) ?>
            </button>
          </h2>
          <div id="<?= $id ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
            <div class="accordion-body"><?= htmlspecialchars($faq['a']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="text-center mt-5">
      <p class="mb-3">Still have questions?</p>
      <a href="contact.php" class="btn btn-maroon btn-lg">Contact Us</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
