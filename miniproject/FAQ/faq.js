document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('h3');
        const answer = item.querySelector('.faq-answer');
        const icon = question.querySelector('i');

        question.addEventListener('click', () => {
            // Toggle active class
            item.classList.toggle('active');

            // Toggle icon
            icon.classList.toggle('fas fa-paw');
            icon.classList.toggle('fas fa-paw');

            // Toggle answer visibility
            if (item.classList.contains('active')) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                answer.style.opacity = '1';
            } else {
                answer.style.maxHeight = '0';
                answer.style.opacity = '0';
            }

            // Close other open items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    otherAnswer.style.maxHeight = '0';
                    otherAnswer.style.opacity = '0';
                    otherItem.querySelector('i').classList.remove('fas fa-paw');
                    otherItem.querySelector('i').classList.add('fas fa-paw');
                }
            });
        });
    });
});