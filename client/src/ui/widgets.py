import tkinter as tk
from ..utils.helpers import get_font

class RoundedButton(tk.Canvas):
    """Custom rounded button using Canvas"""
    
    def __init__(self, parent, text, command, bg_color, fg_color, 
                 hover_color, width=160, height=44, radius=12, font_size=12, **kwargs):
        super().__init__(parent, width=width, height=height, 
                        bg=parent.cget("bg"), highlightthickness=0, **kwargs)
        
        self.command = command
        self.bg_color = bg_color
        self.fg_color = fg_color
        self.hover_color = hover_color
        self.current_bg = bg_color
        self.width = width
        self.height = height
        self.radius = radius
        self.text = text
        self.font_size = font_size
        
        self._draw()
        
        self.bind("<Enter>", self._on_enter)
        self.bind("<Leave>", self._on_leave)
        self.bind("<Button-1>", self._on_click)
    
    def _draw(self):
        self.delete("all")
        # Draw rounded rectangle
        self._create_rounded_rect(2, 2, self.width-2, self.height-2, 
                                  self.radius, fill=self.current_bg, outline="")
        # Draw text
        self.create_text(self.width//2, self.height//2, text=self.text,
                        fill=self.fg_color, font=get_font(self.font_size, "bold"))
    
    def _create_rounded_rect(self, x1, y1, x2, y2, r, **kwargs):
        points = [
            x1+r, y1, x2-r, y1,
            x2, y1, x2, y1+r,
            x2, y2-r, x2, y2,
            x2-r, y2, x1+r, y2,
            x1, y2, x1, y2-r,
            x1, y1+r, x1, y1,
        ]
        return self.create_polygon(points, smooth=True, **kwargs)
    
    def _on_enter(self, event):
        self.current_bg = self.hover_color
        self._draw()
        self.config(cursor="hand2")
    
    def _on_leave(self, event):
        self.current_bg = self.bg_color
        self._draw()
    
    def _on_click(self, event):
        if self.command:
            self.command()
    
    def set_text(self, text):
        self.text = text
        self._draw()


class BentoCard(tk.Canvas):
    """
    A 'Bento Grid' style card with rounded corners and a drop shadow.
    It contains a 'container' Frame where you place your widgets.
    """
    def __init__(self, parent, width, height, radius=20, bg_color="#FFFFFF", shadow_color="#00000010", shadow_offset=4, **kwargs):
        super().__init__(parent, width=width, height=height, 
                         bg=parent.cget("bg"), highlightthickness=0, **kwargs)
        
        self.width = width
        self.height = height
        self.radius = radius
        self.bg_color = bg_color
        self.shadow_color = shadow_color
        self.shadow_offset = shadow_offset
        
        # Draw the card graphics
        self._draw_initial()
        
        # Create a frame inside to hold content
        self.container = tk.Frame(self, bg=self.bg_color)
        
        # Calculate available area for the inner container so it doesn't overlap borders/shadows
        container_w = width - (shadow_offset * 3) 
        container_h = height - (shadow_offset * 3)
        
        self.create_window(width//2 - shadow_offset + 2, height//2 - shadow_offset + 2, 
                           window=self.container, 
                           width=container_w, height=container_h)

    def _draw_initial(self):
        # 1. Shadow Layer (Lower right offset)
        shadow_x1 = self.shadow_offset
        shadow_y1 = self.shadow_offset
        shadow_x2 = self.width
        shadow_y2 = self.height
        
        self.shadow_item = self._create_rounded_rect(
            shadow_x1, shadow_y1, shadow_x2, shadow_y2, 
            self.radius, fill="#D1D5DB", outline=""
        )

        # 2. Main Card Layer (Top left)
        card_x1 = 0
        card_y1 = 0
        card_x2 = self.width - self.shadow_offset
        card_y2 = self.height - self.shadow_offset
        
        self.card_item = self._create_rounded_rect(
            card_x1, card_y1, card_x2, card_y2, 
            self.radius, fill=self.bg_color, outline=""
        )

    def set_background_color(self, color):
        self.bg_color = color
        self.itemconfig(self.card_item, fill=color)
        self.container.configure(bg=color)

    def _create_rounded_rect(self, x1, y1, x2, y2, r, **kwargs):
        points = [
            x1+r, y1, x2-r, y1,
            x2, y1, x2, y1+r,
            x2, y2-r, x2, y2,
            x2-r, y2, x1+r, y2,
            x1, y2, x1, y2-r,
            x1, y1+r, x1, y1,
        ]
        return self.create_polygon(points, smooth=True, **kwargs)
