import React, { useState, useRef, useEffect } from "react";
import "../../../css/message.css";
import { useMutation } from "@tanstack/react-query";
import { toast } from "react-hot-toast";

const Message = ({ messages = [], conversationId, user, onMessageSent }) => {
    const [showFileInput, setShowFileInput] = useState(false);
    const [selectedFile, setSelectedFile] = useState(null);
    const [message, setMessage] = useState("");

    const messagesEndRef = useRef(null);


    useEffect(() => {
        scrollToBottom();
    }, [messages]);
    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    const handleFileInput = () => {
        setShowFileInput(!showFileInput);
    };

    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (file) {
            setSelectedFile(file);
        }
        setShowFileInput(false);
    };

    const handleSendMessage = () => {
        if (!conversationId || !user?.id) {
            toast.error("Please select a conversation first");
            return;
        }
        if (message || selectedFile) {
            sendMessage({ content: message, file: selectedFile, conversationId, sender: user.id });
            setMessage("");
            setSelectedFile(null);
        } else {
            toast.error("Message content or file is required");
        }
    };

    const { mutate: sendMessage, isLoading } = useMutation({
        mutationFn: async ({ content, file, conversationId, sender }) => {
            const formData = new FormData();
            formData.append("content", content);
            formData.append("conversation_id", conversationId);
            formData.append("sender", sender);
            if (file) {
                formData.append("file", file);
            }

            const res = await fetch("/api/sendMessage", {
                method: "POST",
                body: formData,
                credentials: "include",
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || "Something went wrong");
            }

            toast.success("Message sent successfully");
            return data;
        },
        onSuccess: () => {
            onMessageSent();
        },
        onError: (error) => {
            toast.error(error.message);
        },
    });

    return (
        <div className="messages-page">
            <div className="messages-container">
                {messages.length > 0 ? (
                    [...messages].reverse().map((msg, index) => (
                        <div key={index}>
                            {msg.created_at && <p className="message-date">{msg.created_at}</p>}
                            <div className={`message ${msg.isSender ? "sent" : "received"}`}>{!msg.isSender && (
                                <img
                                    src={msg.avatar || "/path/to/default-avatar.jpg"}
                                    alt="Avatar"
                                    className="avatar"
                                />
                            )}
                                <div className={`message-bubble ${
                                    msg.isSender ? "bubble-sent" : "bubble-received"}`}>{msg.content}
                                </div>
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="no-messages">No messages yet. Start the conversation!</div>
                )}
                <div ref={messagesEndRef}/>
            </div>

            <div className="footer">
                <button className="footer-icon" onClick={handleFileInput}>
                    <i className="fas fa-plus-circle"></i>
                </button>

                {showFileInput && (
                    <div className="file-input-container">
                        <input
                            type="file"
                            accept="image/*, .pdf, .docx"
                            onChange={handleFileChange}
                            className="file-input"
                        />
                    </div>
                )}

                {selectedFile && (
                    <div className="selected-file">
                        {selectedFile.type.startsWith("image/") ? (
                            <div className="file-preview">
                                <img
                                    src={URL.createObjectURL(selectedFile)}
                                    alt="Selected file preview"
                                    className="selected-file-preview"
                                />
                                <button
                                    className="remove-file-btn"
                                    onClick={() => setSelectedFile(null)}>
                                    X
                                </button>
                            </div>
                        ) : (
                            <div className="file-preview">
                                <p>{selectedFile.name}</p>
                                <button
                                    className="remove-file-btn"
                                    onClick={() => setSelectedFile(null)}>
                                    X
                                </button>
                            </div>
                        )}
                    </div>
                )}

                <input
                    type="text"
                    placeholder="Aa"
                    className="footer-input"
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    disabled={isLoading}
                />
                <button
                    className="footer-icon"
                    onClick={handleSendMessage}
                    disabled={isLoading}>
                    {isLoading ? (
                        <i className="fas fa-spinner fa-spin"></i>
                    ) : (
                        <i className="fas fa-paper-plane"></i>
                    )}
                </button>
            </div>
        </div>
    );
};

export default Message;