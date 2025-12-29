import asyncio
import edge_tts
import sys
import os

# Usage: python tts.py "Text to speak" "output.mp3" [voice]
# Example: python tts.py "Halo, apa kabar?" "public/audio/response.mp3" "id-ID-GadisNeural"

async def main():
    text = sys.argv[1]
    output_file = sys.argv[2]
    voice = sys.argv[3] if len(sys.argv) > 3 else "id-ID-GadisNeural"

    communicate = edge_tts.Communicate(text, voice)
    await communicate.save(output_file)

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python tts.py <text> <output_file> [voice]")
        sys.exit(1)
        
    asyncio.run(main())
