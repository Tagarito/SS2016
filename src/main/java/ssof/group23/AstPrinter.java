package ssof.group23;

import org.antlr.v4.runtime.tree.TerminalNode;
import ssof.group23.PHPParser;
import org.antlr.v4.runtime.ParserRuleContext;
import org.antlr.v4.runtime.RuleContext;
import org.antlr.v4.runtime.tree.ParseTree;

public class AstPrinter {

    private boolean ignoringWrappers = true;

    public void setIgnoringWrappers(boolean ignoringWrappers) {
        this.ignoringWrappers = ignoringWrappers;
    }

    public void print(RuleContext ctx) {
        explore(ctx, 0);
    }

    private void explore(RuleContext ctx, int indentation) {
        boolean toBeIgnored = ignoringWrappers
                && ctx.getChildCount() == 1
                && ctx.getChild(0) instanceof ParserRuleContext;

        // get line number
        int line = ((ParserRuleContext) ctx).getStart().getLine();

        String ruleName = PHPParser.ruleNames[ctx.getRuleIndex()];
        if (!toBeIgnored) {
            for (int i = 0; i < indentation; i++) {
                System.out.print("  ");
            }
            System.out.println(line + ": " +ruleName);
        }
        for (int i=0;i<ctx.getChildCount();i++) {
            ParseTree element = ctx.getChild(i);
            if (element instanceof RuleContext) {
                explore((RuleContext)element, indentation + (toBeIgnored ? 0 : 1));
            } else {
                if(element instanceof TerminalNode && !ruleName.equals("htmlElement")) {
                    TerminalNode leaf = (TerminalNode) element;

                    for (int j = 0; j < indentation + 1; j++) {
                        System.out.print("  ");
                    }

                    System.out.println(line + ": " + "(TOKEN) " + leaf.getText());
                }
            }
        }
    }

}
