import './bootstrap';

// resources/js/app.js

const initPrinting = () => {
    if (window.printReport) return; // منع التكرار

    window.printReport = function (sectionId, includeHeader = true, printName = 'report') {
        const sectionElement = document.getElementById(sectionId);
        const headerElement = includeHeader ? document.getElementById('printable-header') : null;

        if (!sectionElement) {
            console.error('محتوى الطباعة غير موجود: ' + sectionId);
            return;
        }

        const header = headerElement ? headerElement.innerHTML : '';
        const content = sectionElement.innerHTML;

        let styles = '';
        document.querySelectorAll('link[rel="stylesheet"], style').forEach(node => {
            if (node.tagName === 'LINK') {
                let absoluteHref = new URL(node.getAttribute('href'), document.baseURI).href;
                styles += `<link rel="stylesheet" href="${absoluteHref}">\n`;
            } else {
                styles += node.outerHTML + '\n';
            }
        });

        const printWindow = window.open('', '_blank', 'width=1200,height=800');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html dir="rtl">
                <head>
                    <title>${printName}</title>
                    <meta charset="utf-8">
                    ${styles}
                </head>
                <body style="background:white !important; padding:20px;">
                    <div id="report-content" class="w-full">
                        ${header ? `<div style="margin-bottom: 20px;">${header}</div>` : ''}
                        ${content}
                    </div>
                    <script>
                        window.onload = function() {
                            setTimeout(() => { window.print(); window.close(); }, 800);
                        };
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    };
};

// التشغيل مع أحداث Livewire و Alpine
document.addEventListener('alpine:init', initPrinting);
document.addEventListener('livewire:navigated', initPrinting);
initPrinting();