"""Package only the runtime files, including admin settings and compiled assets."""
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import re

root = Path(__file__).resolve().parent.parent
version = re.search(r'Version:\s*(\S+)', (root / 'vertical-scroll-gallery.php').read_text()).group(1)
required = ['vertical-scroll-gallery.php', 'admin/admin-settings.php', 'readme.txt',
            'build/index.js', 'build/index.asset.php', 'build/style-index.css']
for name in required:
    if not (root / name).is_file():
        raise SystemExit(f'Missing runtime file: {name}. Run npm run build first.')
output = root / 'dist' / f'vertical-scroll-gallery-{version}.zip'
output.parent.mkdir(exist_ok=True)
files = [root / 'vertical-scroll-gallery.php', root / 'readme.txt']
files += sorted((root / 'admin').rglob('*.php'))
files += sorted(p for p in (root / 'build').rglob('*') if p.is_file() and not p.name.endswith('.map'))
with ZipFile(output, 'w', ZIP_DEFLATED) as archive:
    for path in files:
        archive.write(path, Path('vertical-scroll-gallery') / path.relative_to(root))
with ZipFile(output) as archive:
    assert archive.testzip() is None
    assert all('vertical-scroll-gallery/' + name in archive.namelist() for name in required)
print(output)
