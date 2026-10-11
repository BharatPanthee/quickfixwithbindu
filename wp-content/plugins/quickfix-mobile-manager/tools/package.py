"""Build a WordPress-installable ZIP without local runtime files."""
from pathlib import Path
import argparse
import hashlib
import zipfile

parser = argparse.ArgumentParser()
parser.add_argument("destination", type=Path)
args = parser.parse_args()
root = Path(__file__).resolve().parents[1]
args.destination.parent.mkdir(parents=True, exist_ok=True)
files = sorted(p for p in root.rglob("*") if p.is_file()
               and not any(part in {".git", "__pycache__", ".private"}
                           for part in p.relative_to(root).parts)
               and p.suffix not in {".pyc", ".zip", ".log"})
with zipfile.ZipFile(args.destination, "w", zipfile.ZIP_DEFLATED) as package:
    for path in files:
        package.write(path, (Path(root.name) / path.relative_to(root)).as_posix())
with zipfile.ZipFile(args.destination) as package:
    assert package.testzip() is None
    assert "quickfix-mobile-manager/quickfix-mobile-manager.php" in package.namelist()
print(f"Packaged {len(files)} files: {args.destination}")
print("SHA256 " + hashlib.sha256(args.destination.read_bytes()).hexdigest())
