import sys
import json
from meta_ai_api import MetaAI

def main():
    try:
        # Get prompt from arguments
        if len(sys.argv) > 1:
            prompt_text = sys.argv[1]
        else:
            # Or read from stdin if preferred
            prompt_text = sys.stdin.read().strip()

        if not prompt_text:
            print(json.dumps({"error": "No prompt provided"}))
            return

        ai = MetaAI()
        response = ai.prompt(message=prompt_text)
        
        # print the full response as JSON string
        print(json.dumps(response))

    except Exception as e:
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    main()
