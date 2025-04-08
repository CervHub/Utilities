import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';

export default function ChatInterface() {
    const [isLoading, setIsLoading] = useState(false);
    const [chatInput, setChatInput] = useState('');
    const [messages, setMessages] = useState([{ sender: 'bot', text: 'Hola, soy CervIA. ¿En qué puedo ayudarte hoy?' }]);

    const handleChatSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!chatInput.trim()) return;

        const userMessage = { sender: 'user', text: chatInput };
        setMessages((prev) => [...prev, userMessage]);
        setChatInput('');
        setIsLoading(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(route('chat.textToText'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ message: chatInput }),
            });

            if (!response.ok) {
                throw new Error('Error al obtener la respuesta');
            }

            const data = await response.json();

            // Validar que data.message sea un string
            const botMessage = {
                sender: 'bot',
                text: typeof data.message === 'string' ? data.message : 'Lo siento, ocurrió un error inesperado.',
            };

            setMessages((prev) => [...prev, botMessage]);
        } catch (error) {
            setMessages((prev) => [...prev, { sender: 'bot', text: 'Lo siento, ocurrió un error.' }]);
        } finally {
            setIsLoading(false);
        }
    };

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Texto a Voz',
            href: '/text-to-speech',
        },
    ];

    const userName = 'Sofia Davis';
    const userEmail = 'm@example.com';

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map((word) => word[0])
            .join('')
            .toUpperCase();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Texto a Voz" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Card className="bg-card text-card-foreground flex flex-1 flex-col rounded-xl border shadow">
                    <div className="flex flex-row items-center space-x-4 p-6">
                        <div className="flex items-center space-x-4">
                            <span className="bg-primary text-primary-foreground relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full">
                                {getInitials(userName)}
                            </span>
                            <div>
                                <p className="text-sm leading-none font-medium">{userName}</p>
                                <p className="text-muted-foreground text-sm">{userEmail}</p>
                            </div>
                        </div>
                    </div>
                    <ScrollArea className="flex-1 p-6 pt-0" style={{ maxHeight: '580px', overflowY: 'auto' }}>
                        <div className="space-y-4">
                            {messages.map((message, index) => (
                                <div
                                    key={index}
                                    className={`flex w-max max-w-[75%] flex-col gap-2 rounded-lg px-3 py-2 text-sm ${
                                        message.sender === 'user' ? 'bg-primary text-primary-foreground ml-auto' : 'bg-muted'
                                    }`}
                                >
                                    {message.text}
                                </div>
                            ))}
                        </div>
                    </ScrollArea>
                    <div className="flex items-center p-6 pt-0">
                        <form className="flex w-full items-center space-x-2" onSubmit={handleChatSubmit}>
                            <Input
                                id="message"
                                placeholder="Escribe tu mensaje..."
                                autoComplete="off"
                                value={chatInput}
                                onChange={(e) => setChatInput(e.target.value)}
                                disabled={isLoading}
                                className="flex-1"
                            />
                            <Button type="submit" disabled={isLoading} className="h-9 w-9 p-0">
                                {isLoading ? (
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        className="lucide lucide-send"
                                    >
                                        <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                                        <path d="m21.854 2.147-10.94 10.939"></path>
                                    </svg>
                                )}
                                <span className="sr-only">Enviar</span>
                            </Button>
                        </form>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
