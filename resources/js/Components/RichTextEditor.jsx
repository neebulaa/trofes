import { useCallback, useEffect, useMemo, useRef } from "react";
import { LexicalComposer } from "@lexical/react/LexicalComposer";
import { RichTextPlugin } from "@lexical/react/LexicalRichTextPlugin";
import { ContentEditable } from "@lexical/react/LexicalContentEditable";
import { HistoryPlugin } from "@lexical/react/LexicalHistoryPlugin";
import { OnChangePlugin } from "@lexical/react/LexicalOnChangePlugin";
import { ListPlugin } from "@lexical/react/LexicalListPlugin";
import { LinkPlugin } from "@lexical/react/LexicalLinkPlugin";
import { useLexicalComposerContext } from "@lexical/react/LexicalComposerContext";
import { HeadingNode, QuoteNode } from "@lexical/rich-text";
import { ListNode, ListItemNode } from "@lexical/list";
import { LinkNode } from "@lexical/link";
import { $generateHtmlFromNodes, $generateNodesFromDOM } from "@lexical/html";
import Toolbar from "./RichTextEditorToolbar";
import { $getRoot, $createParagraphNode, $createTextNode } from "lexical";

function Placeholder({ text }) {
    return <div className="rte__placeholder">{text}</div>;
}

// hydrates editor from initial html once.
function InitialContentPlugin({ initialHTML }) {
    const [editor] = useLexicalComposerContext();
    const hydratedOnce = useRef(false);

    useEffect(() => {
        if (hydratedOnce.current) return;
        hydratedOnce.current = true;

        editor.update(() => {
            const root = $getRoot();
            root.clear();

            const raw = (initialHTML ?? "").trim();
            if (!raw) return;

            // if its plain text (no tags), just put it into a paragraph
            const looksLikeHTML = /<[^>]+>/.test(raw);
            if (!looksLikeHTML) {
                const p = $createParagraphNode();
                p.append($createTextNode(raw));
                root.append(p);
                return;
            }

            const parser = new DOMParser();
            const dom = parser.parseFromString(raw, "text/html");
            const nodes = $generateNodesFromDOM(editor, dom);

            // root can only accept element/decorator nodes.
            // if we get top-level text nodes, wrap them into a paragraph.
            const paragraph = $createParagraphNode();
            let hasParagraphText = false;

            for (const n of nodes) {
                const type = n.getType?.();

                if (type === "text") {
                    paragraph.append(n);
                    hasParagraphText = true;
                } else {
                    if (hasParagraphText) {
                        root.append(paragraph);
                        hasParagraphText = false;
                    }
                    root.append(n);
                }
            }

            if (hasParagraphText) {
                root.append(paragraph);
            }

            if (root.getChildrenSize() === 0) {
                root.append($createParagraphNode());
            }
        });
    }, [editor, initialHTML]);

    return null;
}

export default function RichTextEditor({
    initialHTML = "",
    onChange,
    placeholder = "Write...",
}) {
    const initialConfig = useMemo(
        () => ({
            namespace: "GuideEditor",
            onError: (e) => console.error(e),
            nodes: [HeadingNode, QuoteNode, ListNode, ListItemNode, LinkNode],
            theme: {
                text: {
                    bold: "rte__bold",
                    italic: "rte__italic",
                    underline: "rte__underline",
                    strikethrough: "rte__strike",
                },
                link: "rte__link",
            },
        }),
        []
    );

    const handleChange = useCallback(
        (editorState, editor) => {
            editorState.read(() => {
                const html = $generateHtmlFromNodes(editor, null);
                onChange?.(html);
            });
        },
        [onChange]
    );

    return (
        <LexicalComposer initialConfig={initialConfig}>
            <div className="rte">
                <Toolbar />
                <div className="rte__body">
                    <RichTextPlugin
                        contentEditable={
                            <ContentEditable className="rte__editable" />
                        }
                        placeholder={<Placeholder text={placeholder} />}
                    />
                    <HistoryPlugin />
                    <ListPlugin />
                    <LinkPlugin />
                    <OnChangePlugin onChange={handleChange} />
                    <InitialContentPlugin initialHTML={initialHTML} />
                </div>
            </div>
        </LexicalComposer>
    );
}
