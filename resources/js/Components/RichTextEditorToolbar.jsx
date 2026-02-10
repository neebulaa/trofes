import { useEffect, useState } from "react";
import {
    FORMAT_TEXT_COMMAND,
    SELECTION_CHANGE_COMMAND,
    $getSelection,
    $isRangeSelection,
} from "lexical";
import { mergeRegister } from "@lexical/utils";
import { useLexicalComposerContext } from "@lexical/react/LexicalComposerContext";
import {
    INSERT_ORDERED_LIST_COMMAND,
    INSERT_UNORDERED_LIST_COMMAND,
    REMOVE_LIST_COMMAND,
} from "@lexical/list";
import { TOGGLE_LINK_COMMAND } from "@lexical/link";

export default function Toolbar() {
    const [editor] = useLexicalComposerContext();

    const [isBold, setIsBold] = useState(false);
    const [isItalic, setIsItalic] = useState(false);
    const [isUnderline, setIsUnderline] = useState(false);

    // Update toolbar based on current selection formatting
    useEffect(() => {
        return mergeRegister(
            editor.registerUpdateListener(({ editorState }) => {
                editorState.read(() => {
                    const selection = $getSelection();
                    if (!$isRangeSelection(selection)) return;

                    setIsBold(selection.hasFormat("bold"));
                    setIsItalic(selection.hasFormat("italic"));
                    setIsUnderline(selection.hasFormat("underline"));
                });
            }),
            editor.registerCommand(
                SELECTION_CHANGE_COMMAND,
                () => {
                    const selection = $getSelection();
                    if (!$isRangeSelection(selection)) return false;

                    setIsBold(selection.hasFormat("bold"));
                    setIsItalic(selection.hasFormat("italic"));
                    setIsUnderline(selection.hasFormat("underline"));
                    return false;
                },
                1
            )
        );
    }, [editor]);

    const run = (command, payload) => editor.dispatchCommand(command, payload);

    const promptLink = () => {
        const url = window.prompt("Enter URL (https://...):");
        if (!url) return;
        run(TOGGLE_LINK_COMMAND, url);
    };

    const removeLink = () => run(TOGGLE_LINK_COMMAND, null);

    return (
        <div className="rte__toolbar">
            <button
                type="button"
                className={`rte__toolBtn ${isBold ? "is-active" : ""}`}
                title="Bold"
                onClick={() => run(FORMAT_TEXT_COMMAND, "bold")}
            >
                <i className="fa-solid fa-bold" />
            </button>

            <button
                type="button"
                className={`rte__toolBtn ${isItalic ? "is-active" : ""}`}
                title="Italic"
                onClick={() => run(FORMAT_TEXT_COMMAND, "italic")}
            >
                <i className="fa-solid fa-italic" />
            </button>

            <button
                type="button"
                className={`rte__toolBtn ${isUnderline ? "is-active" : ""}`}
                title="Underline"
                onClick={() => run(FORMAT_TEXT_COMMAND, "underline")}
            >
                <i className="fa-solid fa-underline" />
            </button>

            <div className="rte__sep" />

            <button
                type="button"
                className="rte__toolBtn"
                title="Bullet list"
                onClick={() => run(INSERT_UNORDERED_LIST_COMMAND, undefined)}
            >
                <i className="fa-solid fa-list-ul" />
            </button>

            <button
                type="button"
                className="rte__toolBtn"
                title="Numbered list"
                onClick={() => run(INSERT_ORDERED_LIST_COMMAND, undefined)}
            >
                <i className="fa-solid fa-list-ol" />
            </button>

            <button
                type="button"
                className="rte__toolBtn"
                title="Remove list"
                onClick={() => run(REMOVE_LIST_COMMAND, undefined)}
            >
                <i className="fa-solid fa-list" />
            </button>

            <div className="rte__sep" />

            <button
                type="button"
                className="rte__toolBtn"
                title="Add/Edit link"
                onClick={promptLink}
            >
                <i className="fa-solid fa-link" />
            </button>

            <button
                type="button"
                className="rte__toolBtn"
                title="Remove link"
                onClick={removeLink}
            >
                <i className="fa-solid fa-link-slash" />
            </button>
        </div>
    );
}
