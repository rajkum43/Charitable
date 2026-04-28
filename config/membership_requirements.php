<?php
// Membership Duration Configuration
// यह file membership requirements को define करती है

return [
    // Beti Vivah Aavedan के लिए minimum membership duration (दिनों में)
    'beti_vivah_aavedan' => 0,
    
    // Death Aavedan के लिए minimum membership duration (दिनों में)
    'death_aavedan' => 0, // इसे 365 पर सेट करें यदि आप 1 साल की requirement रखना चाहते हैं
    
    // अन्य schemes के लिए
    'medical_assistance' => 180,
    'education_support' => 90,
];
?>