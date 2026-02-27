import './bootstrap';

window.printWithHeader = function (sectionId, printName = 'report') {
    const headerElement = document.getElementById('printable-header');
    const sectionElement = document.getElementById(sectionId);

    if (!headerElement || !sectionElement) {
        console.error('عذراً، لم يتم العثور على محتوى للطباعة');
        return;
    }

    const header = headerElement.innerHTML;
    const content = sectionElement.innerHTML;

    // تغيير عنوان الصفحة مؤقتاً
    document.title = printName;
    // جلب الستايلات وتحويل المسارات لروابط كاملة
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
                <style>
                    .no-print { display: none !important; }
                    body { background: white !important; padding: 20px; }
                </style>
            </head>
            <body>
                <div id="report-content" class="w-full">
                    <div style="margin-bottom: 30px;">${header}</div>
                    ${content}
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            setTimeout(function() { window.close(); }, 200);
                        }, 800);
                    };
                <\/script>
            </body>
        </html>
    `);

    printWindow.document.close();
};

window.printWithHeader2 = function (sectionId, printName = 'report') {
    const sectionElement = document.getElementById(sectionId);

    if (!sectionElement) {
        console.error('عذراً، لم يتم العثور على محتوى للطباعة');
        return;
    }
    const content = sectionElement.innerHTML;

    // تغيير عنوان الصفحة مؤقتاً
    document.title = printName;
    // جلب الستايلات وتحويل المسارات لروابط كاملة
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
                <style>
                    .no-print { display: none !important; }
                    body { background: white !important; padding: 20px; }
                </style>
            </head>
            <body>
                <div id="report-content" class="w-full">
                    ${content}
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            setTimeout(function() { window.close(); }, 200);
                        }, 800);
                    };
                <\/script>
            </body>
        </html>
    `);

    printWindow.document.close();
};